<?php

namespace Database\Factories;

use App\Models\Artikel; // Import Artikel model
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{

    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'slug' => $this->faker->unique()->slug,
        ];
    }

    public function withArtikel(Artikel $artikel)
    {
        return $this->afterCreating(function (Category $category) use ($artikel) {
            $category->artikels()->attach($artikel);
        });
    }
}
