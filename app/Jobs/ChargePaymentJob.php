<?php

namespace App\Jobs;

use App\Models\Kelas;
use App\Models\Siswa;
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
use App\Jobs\SendWhatsappNotification;

class ChargePaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $kelas_lulus = Kelas::where('name', 'Lulus')->first();

        $siswas = Siswa::whereHas('kelas', function ($query) use ($kelas_lulus) {
            $query->where('id', '!=', $kelas_lulus->id);
        })->get();

        $monthName = Carbon::now()->locale('id')->translatedFormat('F');

        foreach ($siswas as $siswa) {
            DB::beginTransaction();

            try {
                $order_id = Str::uuid();
                $monthNumber = Carbon::now()->locale('id')->translatedFormat('F');
                $category_Spp = JudulPembayaran::where('name', 'SPP')->first();
                $vaNumber = $siswa->nisn . $monthNumber;
                $biaya_admin = ($siswa->spp > 0) ? 5000 : 0;
                $gross_amount = $siswa->spp + $biaya_admin;

                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => "$category_Spp->name {$monthName} {$siswa->name}",
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (empty($siswa->spp) || $siswa->spp <= 0) {
                    DB::commit();
                    continue;
                }

                // Kirim ke Midtrans
                $this->sendPaymentToMidtrans($siswa, $vaNumber, $gross_amount, $order_id, $category_Spp);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error("Gagal memproses pembayaran {$siswa->name}: " . $e->getMessage());
            }
        }
    }

    private function sendPaymentToMidtrans($siswa, $vaNumber, $gross_amount, $order_id, $category_Spp)
    {
        $monthName = Carbon::now()->locale('id_ID')->format('F');

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
            'qris' => [
                'acquirer' => 'gopay',
            ],
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
                    'name' => "Biaya Administrasi Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                    'price' => 5000,
                    'quantity' => 1,
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
                DB::table('charges')
                    ->where('order_id', $order_id)
                    ->update([
                        'va_number' => $responseData['va_numbers'][0]['va_number'] ?? null,
                        'snap_token' => $responseData['token'] ?? null,
                        'transaction_status' => $responseData['transaction_status'],
                        'transaction_id' => $responseData['transaction_id'],
                        'name_action' => $responseData['actions'][0]['name'] ?? null,
                        'method' => $responseData['actions'][0]['method'] ?? null,
                        'url_action' => $responseData['actions'][0]['url'] ?? null,
                    ]);

                // Kirim notifikasi WhatsApp
                SendWhatsappNotification::dispatch($order_id)->delay(now()->addSeconds(10));
            } else {
                logger()->warning("Gagal pembayaran Midtrans untuk {$siswa->name}: " . $responseData['status_message']);
            }
        } catch (\Exception $e) {
            logger()->error("Gagal kirim ke Midtrans untuk {$siswa->name}: " . $e->getMessage());
        }
    }
}
