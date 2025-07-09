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
        sleep(3);
        $this->createChargeStage($category, 2, $stage2, $adminFee);
    }

    private function createChargeStage($category, $stage, $amount, $adminFee)
    {
        try {
            $total = $amount + $adminFee;
            $orderId = Str::uuid()->toString();
            $vaNumber = $this->siswa->nisn . $stage . now()->format('m');
            $transactionStatus = 'pending';
            $sendToMidtrans = true;

            if ($this->siswa->status_dpp === 'LUNAS' || $total <= 0) {
                $transactionStatus = $total <= 0 ? 'free' : 'pay_offline';
                $sendToMidtrans = false;
            }

            DB::table('charges')->insert([
                'id' => $orderId,
                'name' => "{$category->name} Tahap {$stage} - {$this->siswa->name}",
                'order_id' => Str::uuid()->toString(),
                'siswa_id' => $this->siswa->id,
                'gross_amount' => $total,
                'payment_type' => 'bank_transfer',
                'bank' => 'permata',
                'va_number' => $vaNumber,
                'transaction_id' => Str::uuid()->toString(),
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
                $this->sendToMidtrans($category, $stage, $orderId, $total, $vaNumber);
            }
        } catch (\Exception $e) {
            \Log::error("[CREATE ❌] Gagal membuat charge tahap {$stage} untuk {$this->siswa->name}: " . $e->getMessage());
        }
    }

    private function sendToMidtrans($category, $stage, $orderId, $amount, $vaNumber)
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
                'permata' => [
                    'recipient_name' => 'Sekolah Kreatif SD Muhammadiyah 3 Samarinda',
                ],
            ],
            'custom_expiry' => [
                'expiry_duration' => 365,
                'unit' => 'day',
            ],
            'item_details' => [[
                'id' => 1,
                'price' => $amount,
                'quantity' => 1,
                'name' => "DPP Tahap {$stage} - {$this->siswa->name}",
                'category' => $category->name,
                'merchant_name' => 'Sekolah Kreatif SD Muhammadiyah 3 Samarinda',
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
                DB::table('charges')->where('id', $orderId)->update([
                    'bank' => 'permata',
                    'va_number' => $data['permata_va_number'] ?? $vaNumber,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'transaction_time' => $data['transaction_time'] ?? now(),
                    'transaction_status' => $data['transaction_status'] ?? 'pending',
                    'snap_token' => null,
                ]);


                \Log::info("[MIDTRANS ✅] {$this->siswa->name} | Order ID: {$orderId}");
                SendWhatsappNotification::dispatch($orderId)->delay(now()->addSeconds(10));

            } else {
                \Log::warning("[MIDTRANS ⚠️] Response bukan 201 untuk {$this->siswa->name} | Order ID: {$orderId}");
            }
        } catch (\Exception $e) {
            \Log::error("[MIDTRANS ❌] {$this->siswa->name} error: " . $e->getMessage());
        }
    }
}
