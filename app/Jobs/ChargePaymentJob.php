<?php

namespace App\Jobs;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendWhatsappNotification;

class ChargePaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswaId;
    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 60, 300];

    public function __construct($siswa)
    {
        // ✅ Serialize hanya ID, bukan entire model
        $this->siswaId = $siswa instanceof Siswa ? $siswa->id : $siswa;
    }

    public function handle(): void
    {
        try {
            // ✅ Fetch fresh data
            $siswa = Siswa::findOrFail($this->siswaId);

            Log::info('Processing charge payment', [
                'siswaId' => $siswa->id,
                'siswaName' => $siswa->name,
                'spp' => $siswa->spp,
            ]);

            $monthName = Carbon::now()->locale('id')->translatedFormat('F');

            DB::beginTransaction();

            try {
                $order_id = Str::uuid();
                $monthNumber = Carbon::now()->locale('id')->format('m');
                $category_Spp = JudulPembayaran::where('name', 'SPP')->firstOrFail();

                $vaNumber = $this->generateVANumber($siswa->nisn, $monthNumber);
                $biaya_admin = ($siswa->spp > 0) ? 5000 : 0;
                $gross_amount = $siswa->spp + $biaya_admin;

                // ✅ Create charge record
                $charge = Charge::create([
                    'id' => Str::uuid(),
                    'name' => "{$category_Spp->name} {$monthName} {$siswa->name}",
                    'order_id' => $order_id,
                    'siswa_id' => $siswa->id,
                    'gross_amount' => $gross_amount,
                    'payment_type' => 'qris',
                    'bank' => 'gopay',
                    'va_number' => $vaNumber,
                    'transaction_id' => Str::uuid(),
                    'transaction_time' => now(),
                    'fraud_status' => 'accept',
                    'transaction_status' => ($siswa->spp <= 0) ? 'free' : 'pending',
                    'category_payment_id' => $category_Spp->id,
                    'snap_token' => null,
                ]);

                Log::debug('Charge created', [
                    'chargeId' => $charge->id,
                    'orderId' => $order_id,
                ]);

                // ✅ If no payment needed, commit and send WhatsApp
                if (empty($siswa->spp) || $siswa->spp <= 0) {
                    DB::commit();
                    SendWhatsappNotification::dispatch($charge->id)->delay(now()->addSeconds(2));
                    return;
                }

                // ✅ Send to Midtrans
                $this->sendPaymentToMidtrans($siswa, $charge, $vaNumber, $gross_amount, $category_Spp);

                DB::commit();

                Log::info('Charge payment processed successfully', [
                    'siswaId' => $siswa->id,
                    'orderId' => $order_id,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Charge payment processing failed', [
                'siswaId' => $this->siswaId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);
            }
        }
    }

    /**
     * Generate VA Number
     */
    private function generateVANumber(string $nisn, string $monthNumber): string
    {
        $cleaned = preg_replace('/\D/', '', $nisn);
        $shortened = substr($cleaned, -6); // Last 6 digits
        return $shortened . $monthNumber;
    }

    /**
     * Send payment request to Midtrans
     */
    private function sendPaymentToMidtrans(
        Siswa $siswa,
        Charge $charge,
        string $vaNumber,
        int $gross_amount,
        JudulPembayaran $category_Spp
    ): void {
        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $charge->order_id,
                'gross_amount' => $gross_amount,
            ],
            'customer_details' => [
                'first_name' => $siswa->name,
                'email' => $siswa->email ?? 'noemail@school.local',
                'phone' => $siswa->no_hp ?? '0',
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'custom_expiry' => [
                'expiry_duration' => 7,
                'unit' => 'day',
            ],
            'item_details' => [
                [
                    'id' => 1,
                    'price' => $siswa->spp,
                    'quantity' => 1,
                    'name' => "SPP {$monthName} {$siswa->name}",
                    'category' => $category_Spp->name,
                    'merchant_name' => config('app.school_name', 'Sekolah'),
                ],
                [
                    'id' => 2,
                    'name' => 'Biaya Administrasi',
                    'price' => 5000,
                    'quantity' => 1,
                ]
            ],
        ];

        try {
            $client = new Client();
            $server_key = config('midtrans.server_key');
            $midtrans_url = config('midtrans.is_production')
                ? 'https://api.midtrans.com/v2/charge'
                : 'https://api.sandbox.midtrans.com/v2/charge';

            Log::debug('Sending to Midtrans', [
                'orderId' => $charge->order_id,
                'amount' => $gross_amount,
                'siswaId' => $siswa->id,
            ]);

            $response = $client->post($midtrans_url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if ($responseData['status_code'] == 201) {
                // ✅ Update charge with Midtrans response
                $charge->update([
                    'bank' => 'gopay',
                    'snap_token' => $responseData['token'] ?? null,
                    'transaction_status' => $responseData['transaction_status'] ?? 'pending',
                    'transaction_id' => $responseData['transaction_id'] ?? $charge->transaction_id,
                    'name_action' => $responseData['actions'][0]['name'] ?? null,
                    'method' => $responseData['actions'][0]['method'] ?? null,
                    'url_action' => $responseData['actions'][0]['url'] ?? null,
                ]);

                Log::info('Midtrans charge successful', [
                    'orderId' => $charge->order_id,
                    'transactionId' => $responseData['transaction_id'] ?? null,
                ]);

                // ✅ Dispatch WhatsApp notification
                SendWhatsappNotification::dispatch($charge->id)->delay(now()->addSeconds(2));

            } else {
                Log::warning('Midtrans charge failed', [
                    'orderId' => $charge->order_id,
                    'statusCode' => $responseData['status_code'],
                    'statusMessage' => $responseData['status_message'] ?? 'Unknown',
                ]);

                throw new \Exception(
                    "Midtrans error: " . ($responseData['status_message'] ?? 'Unknown error')
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send payment to Midtrans', [
                'siswaId' => $siswa->id,
                'orderId' => $charge->order_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failed
     */
    public function failed(\Throwable $exception)
    {
        Log::critical('ChargePaymentJob permanently failed', [
            'siswaId' => $this->siswaId,
            'error' => $exception->getMessage(),
        ]);

        // Optional: Send alert to admin
        // Notification::route('mail', 'admin@example.com')
        //     ->notify(new ChargePaymentFailedNotification($this->siswaId, $exception));
    }
}
