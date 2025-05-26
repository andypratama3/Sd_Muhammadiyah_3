<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\JudulPembayaran;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JudulPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {

        // kode pembayaran
        // SPP = 1
        // DPP = 2
        // Seragam = 3
        $datas = [

            'SPP',
            'DPP',
            // Biaya seragam
            'Seragam',
            'SPMB',

        ];

        foreach ($datas as $data) {
            JudulPembayaran::create([
                'name' => $data,
                'kode' => random_int(1000, 9999),
                'slug' => Str::slug($data),
            ]);
        }
    }
}
