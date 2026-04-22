<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            KelasSeeder::class,
            JudulPembayaranSeeder::class,
            VisitorSeeder::class,
            ChargeSeeder::class,
            ArtikelSeeder::class,
            BeritaSeed::class,
            SiswaSeeder::class,
            SiswaNewSeeder::class,
            KeuanganSeeder::class,
        ]);
    }
}
