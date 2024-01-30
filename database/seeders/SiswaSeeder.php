<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Siswa::factory()->count(50000)->create()->each(function ($siswa) {
            // Attach each Siswa to a random Kelas
            $kelas = Kelas::inRandomOrder()->first();
            $siswa->kelas()->attach($kelas, ['category_kelas' => 'A']);
        });
    }
}
