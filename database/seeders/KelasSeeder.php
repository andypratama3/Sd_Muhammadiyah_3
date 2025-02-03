<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            Kelas::create([
                'name' => 'Kelas ' . $i,
                'category_kelas' => json_encode(["A", "B", "C", "D", "E", "F"]),
                'slug' => 'kelas-' . $i,
            ]);
        }
    }
}
