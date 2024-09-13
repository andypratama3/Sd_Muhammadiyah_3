<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelas = Kelas::create([
            'id' => Str::uuid(),
            'name' => 'Kelas 1',
            'category_kelas' => 'A',
            'slug' => Str::slug('Kelas 1'),
        ]);

        Siswa::factory()->count(50000)->create()->each(function ($siswa) {
            // Attach each Siswa to a random Kelas
            $kelas = Kelas::inRandomOrder()->first();
            $siswa->kelas()->attach($kelas, ['category_kelas' => 'A']);
        });
    }
}
