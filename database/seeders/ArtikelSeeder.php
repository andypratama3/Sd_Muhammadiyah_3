<?php

namespace Database\Seeders;

use App\Models\Artikel;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ArtikelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artikel::factory()->count(200)->create();
    }
}
