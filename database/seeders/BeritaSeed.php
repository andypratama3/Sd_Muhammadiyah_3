<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Berita;
class BeritaSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Berita::factory(100)->create([
            'judul' => 'Judul Berita 1',
            'desc' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Sint impedit laudantium dolorem, dolores ullam exercitationem ipsam suscipit distinctio incidunt delectus. Officia suscipit perferendis cumque! Aut velit doloremque corporis dolorem numquam.',
            'foto' => 'Berita_test_20240412172357.jpg',
        ]);
    }
}
