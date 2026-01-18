<?php

namespace App\Jobs;

use App\Models\Kelas;
use App\Models\Siswa;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Jobs\SendWhatsappJob;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChargePaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswa;

    // static offset untuk mengatur jeda antar WhatsApp
    private static $waOffset = 0;

    public function __construct($siswa)
    {
        $this->siswa = $siswa;
    }

    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $order_id = Str::uuid();
            $monthNumber = Carbon::now()->locale('id')->format('m');
            $monthName = Carbon::now()->locale('id')->translatedFormat('F');
            $category_Spp = JudulPembayaran::where('name', 'SPP')->first();
            $vaNumber = $this->siswa->nisn . $monthNumber;

            $biaya_admin = ($this->siswa->spp > 0) ? 5000 : 0;
            $gross_amount = $this->siswa->spp + $biaya_admin;

            DB::table('charges')->insert([
                'id' => Str::uuid(),
                'name' => "{$category_Spp->name} {$monthName} {$this->siswa->name}",
                'order_id' => $order_id,
                'siswa_id' => $this->siswa->id,
                'gross_amount' => $gross_amount,
                'payment_type' => 'qris',
                'bank' => 'gopay',
                'va_number' => $vaNumber,
                'transaction_id' => Str::uuid(),
                'transaction_time' => now(),
                'fraud_status' => 'accept',
                'transaction_status' => ($this->siswa->spp <= 0) ? 'free' : 'pending',
                'category_payment_id' => $category_Spp->id,
                'snap_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($this->siswa->spp <= 0) {
                DB::commit();
                return;
            }

            // kirim ke Midtrans
            // $this->sendPaymentToMidtrans($this->siswa, $vaNumber, $gross_amount, $order_id, $category_Spp);
            // kirim ke Bank Kaltimtara

            $this->sendPaymentToBankKaltimtara($this->siswa, $vaNumber, $gross_amount, $order_id, $category_Spp);

            DB::commit();

            /**
             * ================================
             * FIX RATE LIMIT WHATSAPP
             * ================================
             * Tambah delay per pesan:
             * - WA pertama delay 10 detik + (0 * 2)
             * - WA kedua 10 detik + (1 * 2)
             * - WA ketiga 10 detik + (2 * 2)
             * dst...
             */
            $delay = 10 + (self::$waOffset * 2);
            self::$waOffset++;

            SendWhatsappJob::dispatch($order_id)
                ->onQueue('whatsapp')  // queue WA khusus
                ->delay(now()->addSeconds($delay));

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("Gagal memproses pembayaran {$this->siswa->name}: " . $e->getMessage());
        }
    }

    public function sendPaymentToBankKaltimtara($order_id, $vaNumber, $gross_amount, $category_Spp)
    {
        // Implementasi integrasi dengan Bank Kaltimtara
        // Sesuaikan dengan dokumentasi API Bank Kaltimtara
        // 
    }

    private function sendPaymentToMidtrans($siswa, $vaNumber, $gross_amount, $order_id, $category_Spp)
    {
        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $gross_amount,
            ],
            'customer_details' => [
                'first_name' => $siswa->name,
                'email' => $siswa->email,
                'phone' => $siswa->no_hp,
            ],
            'qris' => ['acquirer' => 'gopay'],
            'custom_expiry' => [
                'expiry_duration' => 365,
                'unit' => 'day',
            ],
            'item_details' => [
                [
                    'id' => 1,
                    'price' => $siswa->spp,
                    'quantity' => 1,
                    'name' => "SPP {$monthName} {$siswa->name}",
                    'category' => $category_Spp->name,
                    'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                ],
                [
                    'id' => 2,
                    'price' => 5000,
                    'quantity' => 1,
                    'name' => "Biaya Administrasi",
                ]
            ],
        ];

        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');
        $midtrans_url = config('midtrans.is_production')
            ? 'https://api.midtrans.com/v2/charge'
            : 'https://api.sandbox.midtrans.com/v2/charge';

        try {
            $response = $client->post($midtrans_url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if ($responseData['status_code'] == 201) {
                DB::table('charges')->where('order_id', $order_id)->update([
                    'bank'               => 'gopay',
                    'snap_token'         => $responseData['token'] ?? null,
                    'transaction_status' => $responseData['transaction_status'],
                    'transaction_id'     => $responseData['transaction_id'],
                    'name_action'        => $responseData['actions'][0]['name'] ?? null,
                    'method'             => $responseData['actions'][0]['method'] ?? null,
                    'url_action'         => $responseData['actions'][0]['url'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            logger()->error("Error Midtrans: " . $e->getMessage());
        }
    }
}
