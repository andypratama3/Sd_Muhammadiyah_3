<?php

namespace Database\Factories;

use App\Models\Artikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArtikelFactory extends Factory
{
    protected $model = Artikel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'artikel' => $this->faker->paragraph,
            'image' => asset('storage/img/artikel/Berita_a_20240210170052.jpg'),
            'jumlah_klik' => $this->faker->numberBetween(1, 1000),
            'slug' => Str::slug($this->faker->sentence),
        ];
    }
}
