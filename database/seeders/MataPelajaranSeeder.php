<?php

namespace Database\Seeders;

use App\Models\Pelajaran;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapel = [
            'Bahasa Indonesia',
            'Sholat Duha',
        ];

        $datas = collect($mapel)->map(function ($item) {
            return [
                'id'         => Str::uuid(),
                'name'       => $item,
                'slug'       => Str::slug($item),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        Pelajaran::insert($datas);
    }
}
