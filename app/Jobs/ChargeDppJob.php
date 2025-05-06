<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChargeDppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $siswa;

    /**
     * Create a new job instance.
     */
    public function __construct($siswa)
    {
        $this->siswa = $siswa;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $category_Dpp = JudulPembayaran::where('name', 'DPP')->first();
        if (!$category_Dpp) {
            return;
        }

        DB::beginTransaction();

        try {
            $existingCharges = Charge::where('category_payment_id', $category_Dpp->id)
                ->where('siswa_id', $this->siswa->id)
                ->count();

            if ($existingCharges >= 2) {
                return;
            }

            $dpp = $this->siswa->dpp;
            if ($dpp <= 0) {
                return;
            }

            $dppStage1 = $dpp * 0.80;
            $dppStage2 = $dpp * 0.20;
            $biaya_admin = 5000;
            $totalBiayaAdmin = $biaya_admin * 2;

            // Stage 1 payment (80%)
            $this->createChargeForStage($this->siswa, $category_Dpp, 1, $dppStage1 + $biaya_admin, $dppStage1, $totalBiayaAdmin);

            // Stage 2 payment (20%)
            $this->createChargeForStage($this->siswa, $category_Dpp, 2, $dppStage2 + $biaya_admin, $dppStage2, $totalBiayaAdmin);

            DB::commit();
        } catch (\Exception $e) {
            $this->info('Error creating charge: '. $e->getMessage());

            DB::rollBack();
        }
    }

    /**
     * Create a charge for each stage.
     */
    private function createChargeForStage(Siswa $siswa, $category_Dpp, $stage, $grossAmount, $dppAmount, $totalBiayaAdmin)
    {
        $monthNumber = Carbon::now()->format('m');
        $order_id = Str::uuid();
        $vaNumber = $siswa->nisn . $category_Dpp->code . $monthNumber . $stage;

        // Insert charge into the charges table
        DB::table('charges')->insert([
            'id' => Str::uuid(),
            'name' => "{$category_Dpp->name} {$siswa->name} #{$stage}",
            'order_id' => $order_id,
            'siswa_id' => $siswa->id,
            'gross_amount' => $grossAmount,
            'payment_type' => 'bank_transfer',
            'bank' => 'bca',
            'va_number' => $vaNumber,
            'transaction_id' => Str::uuid(),
            'transaction_time' => now(),
            'fraud_status' => 'accept',
            'transaction_status' => 'pending',
            'category_payment_id' => $category_Dpp->id,
            'snap_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send payment to Midtrans
        if ($dppAmount > 0) {
            $this->sendPaymentToMidtrans($siswa, $vaNumber, $grossAmount, $order_id, $category_Dpp);
        }
    }

    /**
     * Send payment to Midtrans.
     */
    private function sendPaymentToMidtrans(Siswa $siswa, $vaNumber, $grossAmount, $order_id, $category_Dpp)
    {
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $siswa->name,
                'email' => $siswa->email,
                'phone' => $siswa->no_hp,
            ],
            'bank_transfer' => [
                'bank' => 'bca',
                'va_number' => $vaNumber,
            ],
            'custom_expiry' => [
                'expiry_duration' => 365,
                'unit' => 'day',
            ],
            'item_details' => [
                [
                    'id' => 1,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => "DPP {$siswa->name}",
                    'category' => $category_Dpp->name,
                    'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                ]
            ],
        ];

        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            $response = $client->post('https://api.sandbox.midtrans.com/v2/charge', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            $responseData = json_decode($response->getBody(), true);
            if ($responseData['status_code'] == 201) {
                DB::table('charges')
                    ->where('order_id', $responseData['order_id'])
                    ->update([
                        'va_number' => $responseData['va_numbers'][0]['va_number'] ?? $vaNumber,
                        'snap_token' => $responseData['token'] ?? null,
                        'transaction_status' => $responseData['transaction_status'] ?? 'pending',
                        'transaction_id' => $responseData['transaction_id'] ?? null,
                    ]);
            }
        } catch (\Exception $e) {
            $this->info(''. $e->getMessage());

        }
    }
}
