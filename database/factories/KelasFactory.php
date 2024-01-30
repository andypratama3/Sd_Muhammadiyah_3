<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kelas;
use Faker\Generator as Faker;

class KelasFactory extends Factory
{

    public function definition(): array
    {
        $factory->define(Kelas::class, function (Faker $faker) {
            return [
                'name' => $faker->unique()->randomElement(['1', '2', '3', '4', '5', '6']),
            ];
        });
    }
}
