<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Midtrans\CoreApi; // Import Midtrans CoreApi

class ChargePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:charge-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge payments for all students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siswas = Siswa::all();

        foreach ($siswas as $siswa) {
            // Start Transaction
            DB::beginTransaction();

            try {
                // Check if VA exists for the student in the charges table
                $existingCharge = DB::table('siswas')
                    ->where('va_number', $siswa->va_number) // Check by VA number
                    ->first();

                if ($existingCharge) {
                    // Reuse the existing VA
                    $vaNumber = $existingCharge->va_number;
                } else {
                    // Generate a new VA if it doesn't exist
                    $vaNumber = $this->generateNewVaNumber($siswa);
                    $siswa->update(['va_number' => $vaNumber]);
                }

                // Insert the charge record
                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => 'SPP',
                    'order_id' => rand(),
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



                // Send to API payment Midtrans
                $this->sendPaymentToMidtrans($siswa, $vaNumber);

                // Commit Transaction
                DB::commit();
            } catch (\Exception $e) {
                // Rollback Transaction if something goes wrong
                DB::rollBack();
                $this->error('Failed to charge payment for student ' . $siswa->name . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Send payment to Midtrans API.
     *
     * @param Siswa $siswa
     * @param string $vaNumber
     */
    private function sendPaymentToMidtrans(Siswa $siswa, $vaNumber)
    {
        $params = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => Str::uuid(),
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
                'start_time' => now()->format('Y-m-d H:i:s T'), // Current time
                'duration' => 30, // 30 days
                'unit' => 'days', // Set unit as days
            ],
        ];

        // Guzzle client
        $client = new Client();
        $server_key = env('MIDTRANS_SERVER_KEY');
        try {

            $this->info('Charging payment for student ' . $siswa->name . ' with VA number ' . '80391'.$vaNumber);

            $this->info('expired ' . $params['expiry']['duration'] . ' ' . $params['expiry']['unit'] . 'day');
            // update va number in siswa table
            DB::table('charges')->where('siswa_id', $siswa->id)->update(['va_number' => '80391'.$vaNumber]);
            $this->info('Charging virtual account for student ' . $siswa->name . ' with VA number ' . '80391'.$vaNumber);


            // send virtual account with message to whatsapp for payment
            // $this->SendOrderIDWhatsAppApi($siswa->id);

            // Send the request using Guzzle
            $response = $client->post('https://api.sandbox.midtrans.com/v2/charge', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($server_key . ':'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);

            // Decode the response
            $responseData = json_decode($response->getBody(), true);

            // Log the response
            if (isset($responseData['transaction_status'])) {
                $this->info('Payment charged successfully for student ' . $siswa->name . ': ' . $responseData['transaction_status']);
            } else {
                $this->warn('No transaction_status found in the response for student ' . $siswa->name);
            }
        } catch (\Exception $e) {
            $this->error('Failed to charge payment for student ' . $siswa->name . ': ' . $e->getMessage());
        }
    }

    /**
     * Generate a new VA number for the student.
     *
     * @param Siswa $siswa
     * @return string
     */
    private function generateNewVaNumber(Siswa $siswa)
    {
        return '80391'. rand(1000000000, 9999999999);
    }
}
