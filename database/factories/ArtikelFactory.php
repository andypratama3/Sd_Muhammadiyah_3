<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Artikel;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtikelFactory extends Factory
{
    protected $model = Artikel::class;

    public function definition()
    {

        $user = User::where('name', 'Superadmin')->first();
        return [
            'name' => $this->faker->sentence,
            'artikel' => $this->faker->paragraph,
            'image' => 'Artikel_timon-romero_20240726110831.jpg',
            'user_id' => $user->id,
            'jumlah_klik' => $this->faker->numberBetween(1, 1000),
            'slug' => Str::slug($this->faker->sentence),
            'status' => 'publish',
        ];
    }
}
