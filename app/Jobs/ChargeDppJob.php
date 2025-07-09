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
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ChargeDppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswa;

    public function __construct(Siswa $siswa)
    {
        $this->siswa = $siswa;
    }

    public function handle()
    {
        $category = JudulPembayaran::where('name', 'DPP')->first();
        if (!$category) return;

        $existing = Charge::where('category_payment_id', $category->id)
            ->where('siswa_id', $this->siswa->id)
            ->count();

        if ($existing >= 2) {
            \Log::info("[SKIP] {$this->siswa->name} sudah punya 2 tagihan DPP.");
            return;
        }

        $dpp = $this->siswa->dpp;
        if ($dpp <= 0) return;

        $adminFee = 5000;
        $stage1 = $dpp * 0.80;
        $stage2 = $dpp * 0.20;

        $this->createChargeStage($category, 1, $stage1, $adminFee);

        // ✅ Tambahkan delay aman antar request ke Midtrans
        sleep(3);

        $this->createChargeStage($category, 2, $stage2, $adminFee);
    }

    private function createChargeStage($category, $stage, $amount, $adminFee)
    {
        $total = $amount + $adminFee;
        $orderId = Str::uuid();
        $vaNumber = $this->siswa->nisn . $stage . now()->format('m');
        $transactionStatus = 'pending';
        $sendToMidtrans = true;

        if ($this->siswa->status_dpp === 'LUNAS' || $total <= 0) {
            $transactionStatus = $total <= 0 ? 'free' : 'pay_offline';
            $sendToMidtrans = false;
        }

        DB::table('charges')->insert([
            'id' => Str::uuid(),
            'name' => "{$category->name} Tahap {$stage} - {$this->siswa->name}",
            'order_id' => $orderId,
            'siswa_id' => $this->siswa->id,
            'gross_amount' => $total,
            'payment_type' => 'bank_transfer',
            'bank' => 'permata',
            'va_number' => $vaNumber,
            'transaction_id' => Str::uuid(),
            'transaction_time' => now(),
            'fraud_status' => 'accept',
            'transaction_status' => $transactionStatus,
            'category_payment_id' => $category->id,
            'snap_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Log::info("[CREATE] DPP Tahap {$stage} untuk {$this->siswa->name}");

        if ($sendToMidtrans) {
            $this->sendToMidtrans($category, $orderId, $total, $vaNumber);
        }
    }

    private function sendToMidtrans($category, $orderId, $amount, $vaNumber)
    {
        $client = new Client();
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $url = config('midtrans.is_production')
            ? 'https://api.midtrans.com/v2/charge'
            : 'https://api.sandbox.midtrans.com/v2/charge';

        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
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
            'item_details' => [[
                'id' => 1,
                'price' => $amount,
                'quantity' => 1,
                'name' => "DPP {$this->siswa->name}",
                'category' => $category->name,
                'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
            ]],
        ];

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data['status_code'] == 201) {
                DB::table('charges')->where('order_id', $orderId)->update([
                    'va_number' => $data['va_numbers'][0]['va_number'] ?? $vaNumber,
                    'snap_token' => $data['token'] ?? null,
                    'transaction_status' => $data['transaction_status'] ?? 'pending',
                    'transaction_id' => $data['transaction_id'] ?? null,
                ]);

                \Log::info("[MIDTRANS ✅] {$this->siswa->name} | {$orderId}");
            } else {
                \Log::warning("[MIDTRANS ⚠️] Response tidak 201 untuk {$this->siswa->name} | {$orderId}");
            }
        } catch (\Exception $e) {
            \Log::error("[MIDTRANS ❌] {$this->siswa->name} error: " . $e->getMessage());
        }
    }
}
