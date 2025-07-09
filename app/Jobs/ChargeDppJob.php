<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWhatsappNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChargeDppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // public $queue = 'dpp'; // Optional: agar bisa dipisah dari queue lain
    protected $siswa;

    public function __construct(Siswa $siswa)
    {
        $this->siswa = $siswa;
    }

    public function handle()
    {
        $category_Dpp = JudulPembayaran::where('name', 'DPP')->first();
        if (!$category_Dpp) return;

        $existingCharges = Charge::where('category_payment_id', $category_Dpp->id)
            ->where('siswa_id', $this->siswa->id)
            ->count();

        if ($existingCharges >= 2) {
            \Log::info("Siswa {$this->siswa->name} sudah memiliki 2 tagihan DPP.");
            return;
        }

        $dpp = $this->siswa->dpp;
        if ($dpp <= 0) return;

        $dppStage1 = $dpp * 0.80;
        $dppStage2 = $dpp * 0.20;
        $biaya_admin = 5000;

        // Tahap 1
        $this->createChargeForStage($category_Dpp, 1, $dppStage1, $biaya_admin);
        sleep(3);
        // Tahap 2
        $this->createChargeForStage($category_Dpp, 2, $dppStage2, $biaya_admin);
    }

    private function createChargeForStage($category_Dpp, $stage, $dppAmount, $biaya_admin)
    {
        $grossAmount = $dppAmount + $biaya_admin;
        $order_id = Str::uuid();
        $vaNumber = $this->siswa->nisn . $stage . now()->format('m');
        $transactionStatus = 'pending';
        $sendToMidtrans = true;

        if ($this->siswa->status_dpp == 'LUNAS') {
            $transactionStatus = 'pay_offline';
            $sendToMidtrans = false;
        }

        if ($grossAmount == 0) {
            $transactionStatus = 'free';
            $sendToMidtrans = false;
        }

        DB::table('charges')->insert([
            'id' => Str::uuid(),
            'name' => "{$category_Dpp->name} Tahap {$stage} - {$this->siswa->name}",
            'order_id' => $order_id,
            'siswa_id' => $this->siswa->id,
            'gross_amount' => $grossAmount,
            'payment_type' => 'bank_transfer',
            'bank' => 'permata',
            'va_number' => $vaNumber,
            'transaction_id' => Str::uuid(),
            'transaction_time' => now(),
            'fraud_status' => 'accept',
            'transaction_status' => $transactionStatus,
            'category_payment_id' => $category_Dpp->id,
            'snap_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Log::info("Tagihan DPP Tahap {$stage} untuk {$this->siswa->name} berhasil dibuat.");

        if ($sendToMidtrans) {
            $this->sendToMidtrans($category_Dpp, $order_id, $grossAmount, $vaNumber);
        }
    }

    private function sendToMidtrans($category_Dpp, $order_id, $grossAmount, $vaNumber)
    {
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $this->siswa->name,
                'email' => $this->siswa->email,
                'phone' => $this->siswa->no_hp,
            ],
            'bank_transfer' => [
                'bank' => 'permata',
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
                    'name' => "DPP {$this->siswa->name}",
                    'category' => $category_Dpp->name,
                    'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                ]
            ],
        ];

        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');
        $url = config('midtrans.is_production')
            ? 'https://api.midtrans.com/v2/charge'
            : 'https://api.sandbox.midtrans.com/v2/charge';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            $data = json_decode($response->getBody(), true);


            if ($data['status_code'] == 201) {
                DB::table('charges')->where('order_id', $order_id)->update([
                    'va_number' => $data['va_numbers'][0]['va_number'] ?? $vaNumber,
                    'snap_token' => $data['token'] ?? null,
                    'transaction_status' => $data['transaction_status'] ?? 'pending',
                    'transaction_id' => $data['transaction_id'] ?? null,
                ]);


                \Log::info("Midtrans berhasil untuk DPP {$this->siswa->name} order_id: {$order_id}");

                // Kirim notifikasi WhatsApp
                // SendWhatsappNotification::dispatch($order_id)->delay(now()->addSeconds(10));
            }
        } catch (\Exception $e) {
            \Log::error("Midtrans error untuk {$this->siswa->name}: " . $e->getMessage());
        }
    }
}
