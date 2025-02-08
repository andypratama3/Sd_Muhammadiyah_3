<?php
namespace App\Console\Commands;

use App\Models\Siswa;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Midtrans\CoreApi;

class ChargePayment extends Command
{
    protected $signature = 'app:charge-payment';
    protected $description = 'Charge payments for all students';

    public function handle()
    {
        $siswas = Siswa::all();

        foreach ($siswas as $siswa) {
            DB::beginTransaction();

            try {
                // Periksa apakah siswa sudah punya VA Number
                if (!$siswa->va_number) {
                    $vaNumber = $this->generateNewVaNumber();
                    $siswa->update(['va_number' => $vaNumber]);
                } else {
                    $vaNumber = $siswa->va_number;
                }

                $order_id = Str::uuid();

                // Insert data ke tabel charges
                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => 'SPP',
                    'order_id' => $order_id,
                    'siswa_id' => $siswa->id,
                    'gross_amount' => $siswa->spp,
                    'payment_type' => 'bank_transfer',
                    'bank' => 'bca',
                    'va_number' => $vaNumber,
                    'transaction_id' => Str::uuid(),
                    'transaction_time' => now(),
                    'fraud_status' => 'accept',
                    'transaction_status' => 'pending',
                    'snap_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kirim pembayaran ke Midtrans
                $this->sendPaymentToMidtrans($siswa, $vaNumber, $order_id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}: " . $e->getMessage());
            }
        }
    }

    private function sendPaymentToMidtrans(Siswa $siswa, $vaNumber, $order_id)
    {
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $siswa->spp,
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
            'expiry' => [
                'start_time' => now()->toIso8601String(),
                'duration' => 20,
                'unit' => 'days',
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
                $this->info("VA untuk {$siswa->name}: " . $responseData['va_numbers'][0]['va_number']);
                $this->info("Transaksi akan kedaluwarsa dalam {$params['expiry']['duration']} {$params['expiry']['unit']}");


                DB::table('charges')
                    ->where('order_id', $responseData['order_id'])
                    ->update([
                        'va_number' => $responseData['va_numbers'][0]['va_number'],
                        'snap_token' => $responseData['token'] ?? null,
                        'transaction_status' => $responseData['transaction_status'],
                    ]);

                $this->info("Pembayaran untuk {$siswa->name} berhasil dikirim ke Midtrans.");
            } else {
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}: " . $responseData['status_message']);
            }
        } catch (\Exception $e) {
            $this->error("Gagal mengirim pembayaran ke Midtrans untuk {$siswa->name}: " . $e->getMessage());
        }
    }

    private function generateNewVaNumber()
    {
        do {
            $vaNumber = rand(1000000000, 9999999999);
            $exists = DB::table('siswas')->where('va_number', $vaNumber)->exists();
        } while ($exists);

        return $vaNumber;
    }
}
