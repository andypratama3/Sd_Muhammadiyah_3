<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChargeDppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:charge-dpp-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siswas = Siswa::all();
        $monthName = Carbon::now()->locale('id_ID')->translatedFormat('F');
        $monthNumber = Carbon::now()->format('m');

        $category_Dpp = JudulPembayaran::where('name', 'DPP')->first();
        if (!$category_Dpp) {
            $this->error("Kategori pembayaran DPP tidak ditemukan.");
            return;
        }

        foreach ($siswas as $siswa) {
            DB::beginTransaction();

            try {
                // Cek apakah sudah ada 2 tagihan DPP
                $existingCharges = Charge::where('category_payment_id', $category_Dpp->id)
                    ->where('siswa_id', $siswa->id)
                    ->count();

                if ($existingCharges >= 2) {
                    $this->info("Sudah ada 2 tagihan DPP untuk {$siswa->name}.");
                    DB::rollBack();
                    continue;
                }

                $dpp = $siswa->dpp;
                if ($dpp <= 0) {
                    $this->warn("Siswa {$siswa->name} memiliki DPP Rp0. Tidak dibuatkan tagihan.");

                    continue;
                }

                // Hitung 80% dan 20% dari DPP
                $dppStage1 = $dpp * 0.80; // 80% untuk tahap pertama
                $dppStage2 = $dpp * 0.20; // 20% untuk tahap kedua
                $biaya_admin = 5000; // Biaya admin tetap
                $totalBiayaAdmin = $biaya_admin * 2; // Biaya admin untuk dua tahap

                // Pembayaran untuk tahap 1 (80%)
                $this->createChargeForStage($siswa, $category_Dpp, 1, $dppStage1 + $biaya_admin, $dppStage1, $totalBiayaAdmin);

                // Pembayaran untuk tahap 2 (20%)
                $this->createChargeForStage($siswa, $category_Dpp, 2, $dppStage2 + $biaya_admin, $dppStage2, $totalBiayaAdmin);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Buat charge untuk tiap tahap
     */
    private function createChargeForStage(Siswa $siswa, $category_Dpp, $stage, $grossAmount, $dppAmount, $totalBiayaAdmin)
    {
        $monthName = Carbon::now()->locale('id_ID')->translatedFormat('F');
        // $monthNumber = Carbon::now()->format('m');

        // Generate order ID dan VA Number
        $order_id = Str::uuid();
        $vaNumber = $siswa->nisn . $stage;

        // Insert data ke tabel charges
        DB::table('charges')->insert([
            'id' => Str::uuid(),
            'name' => "{$category_Dpp->name} {$siswa->name} #{$stage}",
            'order_id' => $order_id,
            'siswa_id' => $siswa->id,
            'gross_amount' => $grossAmount,
            'payment_type' => 'bank_transfer',
            'bank' => 'bri',
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

        // Kirim pembayaran ke Midtrans hanya jika DPP > 0
        if ($dppAmount > 0) {
            $this->sendPaymentToMidtrans($siswa, $vaNumber, $grossAmount, $order_id, $category_Dpp);
        }
    }

    /**
     * Kirim pembayaran ke Midtrans
     */
    private function sendPaymentToMidtrans(Siswa $siswa, $vaNumber, $grossAmount, $order_id, $category_Dpp)
    {
        $monthName = Carbon::now()->locale('id_ID')->translatedFormat('F');

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
                'bank' => 'bri',
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
                    'name' => "DPP {$monthName} {$siswa->name}",
                    'category' => $category_Dpp->name,
                    'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                ]
            ],
        ];

        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');

        try {
            $response = $client->post('https://api.midtrans.com/v2/charge', [
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

                $this->info("Pembayaran untuk {$siswa->name} berhasil dikirim ke Midtrans.");
            } else {
                $this->error("Gagal Midtrans untuk {$siswa->name}: " . ($responseData['status_message'] ?? 'Tidak diketahui'));
            }
        } catch (\Exception $e) {
            $this->error("Gagal mengirim ke Midtrans untuk {$siswa->name}: " . $e->getMessage());
        }
    }

}
