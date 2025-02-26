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
    public function run(): void
    {
        $datas = [
            'SPP',
            'DPP',
            'Seragam',
        ];

        foreach ($datas as $d) {
            JudulPembayaran::create([
                'name' => $d,
                'kode' => random_int(1000, 9999),
                'slug' => Str::slug($d),
            ]);
        }
    }
}
