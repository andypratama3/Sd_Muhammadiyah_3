<?php
namespace App\Console\Commands;

use App\Models\Siswa;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Dashboard\SendOrderIDWhatsAppApi;

class ChargePaymentXendit extends Command
{
    protected $signature = 'app:charge-payment-xendit';
    protected $description = 'Charge payments for all students';

    protected $whatsApp;

    public function __construct(SendOrderIDWhatsAppApi $whatsApp)
    {
        parent::__construct();
        $this->whatsApp = $whatsApp;
    }

    public function handle()
    {
        $siswas = Siswa::all();
        $monthName = Carbon::now()->locale('id_ID')->format('F');

        foreach ($siswas as $siswa) {
            DB::beginTransaction();

            try {
                $order_id = Str::uuid();
                $monthNumber = Carbon::now()->locale('id_ID')->format('m');
                $category_Spp = JudulPembayaran::where('name', 'SPP')->first();
                $vaNumber = $siswa->nisn;

                $biaya_admin = 5000;
                $gross_amount = $siswa->spp + $biaya_admin;

                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => "$category_Spp->name  {$monthName} {$siswa->name}",
                    'order_id' => $order_id,
                    'siswa_id' => $siswa->id,
                    'gross_amount' => $gross_amount,
                    'payment_type' => 'bank_transfer',
                    'bank' => 'bca',
                    'va_number' => $vaNumber,
                    'transaction_id' => Str::uuid(),
                    'transaction_time' => now(),
                    'transaction_status' => 'pending',
                    'category_payment_id' => $category_Spp->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->sendPaymentToXendit($siswa, $vaNumber, $gross_amount, $order_id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}: " . $e->getMessage());
            }
        }
    }

    private function sendPaymentToXendit(Siswa $siswa, $vaNumber, $gross_amount, $order_id)
    {
        $client = new Client();
        $api_key = env('XENDIT_SECRET_KEY');

        $params = [
            'external_id' => $order_id,
            'bank_code' => 'BCA',
            'name' => $siswa->name,
            'expected_amount' => $gross_amount,
            'is_closed' => true, // Wajib true jika ada expected_amount
            'is_single_use' => false,
            'suggested_va_numbers' => [$siswa->nisn], // Gunakan NISN sebagai nomor VA
            'expiration_date' => Carbon::now()->addDays(365)->toIso8601String(),
        ];

        try {
            $response = $client->post('https://api.xendit.co/callback_virtual_accounts', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($api_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['id'])) {
                DB::table('charges')
                    ->where('order_id', $order_id)
                    ->update([
                        'va_number' => $responseData['account_number'],
                        'transaction_status' => 'pending',
                    ]);

                $this->info("Pembayaran untuk {$siswa->name} berhasil dikirim ke Xendit.");
            } else {
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}.");
            }
        } catch (\Exception $e) {
            $this->error("Gagal mengirim pembayaran ke Xendit untuk {$siswa->name}: " . $e->getMessage());
        }
    }
}
