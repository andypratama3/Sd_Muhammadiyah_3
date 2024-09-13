<?php

namespace App\Console\Commands;

use App\Models\Siswa;
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
                    $vaNumber = $this->generateNewVaNumber($siswa); // Assuming this function generates a new VA number
                }

                // Insert the charge record
                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => $siswa->name,
                    'order_id' => rand(),
                    'siswa_id' => $siswa->id,
                    'gross_amount' => $siswa->spp,
                    'payment_type' => 'bank_transfer',
                    'bank' => 'bni',
                    'va_number' => $vaNumber,
                    'transaction_id' => rand(),
                    'transaction_time' => now(),
                    'fraud_status' => 'accept',
                    'transaction_status' => 'pending',
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
                'order_id' => $siswa->id,
                'gross_amount' => $siswa->spp,
            ],
            'customer_details' => [
                'first_name' => $siswa->name,
                'email' => $siswa->email,
                'phone' => $siswa->phone,
            ],
            'bank_transfer' => [
                'bank' => 'bni',
                'va_number' => $vaNumber,
            ],
        ];

        try {
            $response = CoreApi::charge($params); // Charge API request
            $this->info('Payment charged successfully for student ' . $siswa->name . ': ' . $response->transaction_status);
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
        return rand(1000000000, 9999999999); // 10 digits
    }
}
