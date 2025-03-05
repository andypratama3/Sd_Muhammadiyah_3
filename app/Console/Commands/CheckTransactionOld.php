<?php

namespace App\Console\Commands;

use App\Models\Charge;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckTransactionOld extends Command
{
    protected $signature = 'app:check-transaction-old';
    protected $description = 'Cek transaksi pending di Midtrans dan perbarui jika diperlukan';

    public function handle()
    {
        $charges = Charge::where('transaction_status', 'expire')->get();
        $server_key = env('MIDTRANS_SERVER_KEY');
        $client = new Client();

        foreach ($charges as $charge) {
            DB::beginTransaction();
            $order_id_charge = $charge->order_id ?? $charge->order_id_1;
            try {
                $response = $client->get("https://api.midtrans.com/v2/{$order_id_charge}/status", [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                        'Content-Type' => 'application/json',
                    ],
                ]);

                $responseData = json_decode($response->getBody(), true);
                // dd($responseData);
                if ($responseData['transaction_status'] == 'expire' || $responseData['transaction_status'] == 'cancel' || $responseData['transaction_status'] == 'pending') //if ($responseData['transaction_status'] == '['expire', 'pending', 'cancel']') {
                {
                    // cancel transaction in midtrans
                    $response_cancel = $client->post("https://api.midtrans.com/v2/{$order_id_charge}/cancel", [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $response_cancel_data = json_decode($response_cancel->getBody(), true);

                    $statusesToCancel = ['expire', 'cancel'];

                    if (in_array($responseData['transaction_status'], $statusesToCancel)) {
                        $this->sendPaymentToMidtrans($charge);
                    }

                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses transaksi {$charge->order_id}: " . $e->getMessage());
            }
        }
    }

    private function sendPaymentToMidtrans(Charge $charge)
    {
        $monthName = Carbon::now()->locale('id_ID')->format('F');
        // when have va_number have 80391 explode

        if($va_number = explode('80391', $charge->va_number)){
            $charge->va_number = $va_number[1];
        } else {
            $charge->va_number = $charge->va_number;
        }

        $order_id = Str::uuid(); // ID baru untuk transaksi baru
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $charge->gross_amount,

            ],
            'customer_details' => [
                'first_name' => $charge->siswa->name ?? 'Siswa',
                'phone' => $charge->siswa->no_hp,
            ],
            'bank_transfer' => [
                'bank' => 'bca',
                'va_number' => $charge->va_number,
            ],
           'custom_expiry' => [
                'expiry_duration' => 365,
                'unit' => 'day',
            ],
            'item_details' => [
                [
                    'id' => 1,
                    'price' => $charge->gross_amount ?? 0,
                    'quantity' => 1,
                    'name' => $charge->name,
                    'category' => $charge->category_payment_id,
                    'merchant_name' => "Sekolah Kreatif SD Muhammadiyah 3 Samarinda",
                ],
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

            if (!empty($responseData['va_numbers'][0]['va_number'])) {
                DB::table('charges')
                    ->where('order_id', $charge->order_id)
                    ->update([
                        'order_id' => $order_id,
                        'va_number' => $responseData['va_numbers'][0]['va_number'],
                        'snap_token' => $responseData['token'] ?? null,
                        'transaction_status' => $responseData['transaction_status'],
                        'transaction_id' => $responseData['transaction_id'],
                    ]);

                $this->info("Pembayaran untuk {$charge->siswa->name} berhasil dikirim ke Midtrans.");
            } else {
                $this->error("Gagal mendapatkan VA untuk {$charge->siswa->name}");
            }
        } catch (\Exception $e) {
            $this->error("Gagal mengirim pembayaran ke Midtrans: " . $e->getMessage());
        }
    }
}
