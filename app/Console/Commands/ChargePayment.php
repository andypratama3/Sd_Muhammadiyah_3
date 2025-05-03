<?php
namespace App\Console\Commands;

use App\Models\Siswa;
use Midtrans\CoreApi;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\JudulPembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Dashboard\SendOrderIDWhatsAppApi;

class ChargePayment extends Command
{
    protected $signature = 'app:charge-payment';
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

         // make can name automatic by month
        $monthName = Carbon::now()->locale('id_ID')->format('F');

        foreach ($siswas as $siswa) {

            if($siswa->spp == 'null' || $siswa->spp == '' || $siswa->spp == null){
                // don't create charge but continue next siswa
                $this->warn('Siswa ' . $siswa->name . ' tidak memiliki SPP');
                continue;
            }

            DB::beginTransaction();

            try {
                // // Periksa apakah siswa sudah punya VA Number
                // if (!$siswa->va_number) {
                //     $vaNumber = $this->generateNewVaNumber();
                //     $siswa->update(['va_number' => $vaNumber]);
                // } else {
                //     $vaNumber = $siswa->va_number;
                // }


                $order_id = Str::uuid();
                // make number mounth
                $monthNumber = Carbon::now()->locale('id_ID')->format('m');

                $category_Spp = JudulPembayaran::where('name', 'SPP')->first() ;
                $vaNumber = $siswa->nisn . $category_Spp->code . $monthNumber;

                $biaya_admin = 5000;
                $gross_amount = $siswa->spp + $biaya_admin;
                // Insert data ke tabel charges
                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => "$category_Spp->name  {$monthName} {$siswa->name} test 1",
                    'order_id' => $order_id,
                    'siswa_id' => $siswa->id,
                    'gross_amount' => $gross_amount,
                    'payment_type' => 'bank_transfer',
                    'bank' => 'bca',
                    'va_number' => $vaNumber,
                    'transaction_id' => Str::uuid(),
                    'transaction_time' => now(),
                    'fraud_status' => 'accept',
                    'transaction_status' => 'pending',
                    'category_payment_id' => $category_Spp->id,
                    'snap_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Kirim pembayaran ke Midtrans
                $this->sendPaymentToMidtrans($siswa, $vaNumber,$gross_amount, $order_id, $category_Spp);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}: " . $e->getMessage());
            }
        }
    }

    private function sendPaymentToMidtrans(Siswa $siswa, $vaNumber,$gross_amount, $order_id, $category_Spp)
    {
        $monthName = Carbon::now()->locale('id_ID')->format('F');



        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $gross_amount,
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
                // 'order_time' => now()->toIso8601String(),
                'expiry_duration' => 365,
                'unit' => 'day',
            ],
            'item_details' => [
                [
                    'id' => 1,
                    'price' => $siswa->spp,
                    'quantity' => 1,
                    'name' => "SPP  {$monthName} {$siswa->name}",
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
                $this->info("VA untuk {$siswa->name}: " . $responseData['va_numbers'][0]['va_number']);
                // $this->info("Transaksi akan kedaluwarsa dalam {$params['expiry']['duration']} {$params['expiry']['unit']}");


                DB::table('charges')
                    ->where('order_id', $responseData['order_id'])
                    ->update([
                        'va_number' => $responseData['va_numbers'][0]['va_number'],
                        'snap_token' => $responseData['token'] ?? null,
                        'transaction_status' => $responseData['transaction_status'],
                        'transaction_id' => $responseData['transaction_id'],
                    ]);

                $this->info("Pembayaran untuk {$siswa->name} berhasil dikirim ke Midtrans.");

                // mengirim pesan ke WhatsApp
                // $this->whatsApp->sendMessage($responseData['order_id']);

            } else {
                $this->error("Gagal memproses pembayaran untuk {$siswa->name}: " . $responseData['status_message']);
            }
        } catch (\Exception $e) {
            $this->error("Gagal mengirim pembayaran ke Midtrans untuk {$siswa->name}: " . $e->getMessage());
        }
    }

    // private function generateNewVaNumber()
    // {
    //     do {
    //         $vaNumber = rand(1000000000, 9999999999);
    //         $exists = DB::table('siswas')->where('va_number', $vaNumber)->exists();
    //     } while ($exists);

    //     return $vaNumber;
    // }
}
