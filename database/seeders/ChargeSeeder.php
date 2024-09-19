<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Charge;
use App\Models\Siswa;
use Carbon\Carbon;

class ChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $months = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12'
        ];

        $year = date('Y'); // Current year
        $totalRecords = 500; // Total records to generate
        $recordsPerMonth = $totalRecords / count($months);

        foreach ($months as $monthName => $monthNumber) {
            for ($i = 0; $i < $recordsPerMonth; $i++) {
                Charge::create([
                    'name' => $monthName,
                    'order_id' => 'ORD-'.Str::random(5),
                    'siswa_id' => Siswa::all()->random()->id,
                    'gross_amount' => rand(10000, 100000),
                    'payment_type' => 'bank_transfer',
                    'bank' => ['BNI', 'BRI', 'Mandiri'][array_rand(['BNI', 'BRI', 'Mandiri'])],
                    'va_number' => '1234567890',
                    'transaction_id' => 'TRX-'.Str::random(5),
                    'transaction_time' => now(),
                    'fraud_status' => 'accept',
                    'transaction_status' => ['pending', 'success'][array_rand(['pending', 'success'])],
                    'snap_token' => Str::random(10),
                    'created_at' => Carbon::create($year, $monthNumber, rand(1, 28)), // Random day within the month
                ]);
            }
        }
    }
}
