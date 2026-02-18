<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Siswa;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChargeSeeder extends Seeder
{
    public function run()
    {
        $paymentTypes = ['bank_transfer', 'credit_card', 'qris', 'gopay', 'shopeepay', 'cstore'];
        $banks = ['BCA', 'BNI', 'BRI', 'Mandiri', 'Permata'];
        $fraudStatuses = ['accept', 'deny', 'challenge'];
        $transactionStatuses = ['pending', 'settlement', 'capture', 'expire', 'cancel'];

        $siswa = Siswa::first();

        foreach (range(2020, 2025) as $year) {
            for ($i = 0; $i < 10; $i++) {
                $transactionTime = Carbon::create($year, rand(1, 12), rand(1, 28));

                DB::table('charges')->insert([
                    'id' => Str::uuid(),
                    'name' => 'User ' . Str::random(5),
                    'order_id' => Str::uuid(),
                    'order_id_1' => rand(0, 1) ? Str::uuid() : null,
                    'siswa_id' => $siswa->id,
                    'gross_amount' => rand(50000, 500000),
                    'payment_type' => $paymentType = $paymentTypes[array_rand($paymentTypes)],
                    'bank' => in_array($paymentType, ['bank_transfer', 'credit_card', 'qris']) ? $banks[array_rand($banks)] : null,
                    'va_number' => $paymentType == 'bank_transfer' ? (string)rand(1000000000, 9999999999) : null,
                    'transaction_id' => Str::uuid(),
                    'transaction_time' => $transactionTime->toDateString(),
                    'fraud_status' => $fraudStatuses[array_rand($fraudStatuses)],
                    'category_payment_id' => DB::table('judulpembayarans')->inRandomOrder()->first()->id ?? Str::uuid(),
                    'transaction_status' => $transactionStatuses[array_rand($transactionStatuses)],
                    'snap_token' => Str::random(32),
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);
            }
        }
    }
}
