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
                'slug' => Str::slug($d),
            ]);
        }
    }
}
