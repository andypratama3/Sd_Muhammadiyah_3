<?php

namespace Database\Factories;

use App\Models\Artikel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artikel>
 */
class ArtikelFactory extends Factory
{
    protected $model = Artikel::class; // Tentukan model yang digunakan oleh factory

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'artikel' => $this->faker->paragraph,
            'image' => 'https://assets.kompasiana.com/items/album/2020/01/07/image-headline-5e143d68d541df25fb1bbfa2.jpg?t=o&v=740&x=416', // Gantilah dengan path gambar Anda atau logic pembuatan gambar
            'jumlah_klik' => $this->faker->numberBetween(1, 1000),
            'slug' => Str::slug($this->faker->sentence),
        ];
    }
}
