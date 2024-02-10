<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ArtikelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 100 new Artikel instances
        $artikels = Artikel::factory(100)->create();

        // Create a new Category instance
        $category = Category::factory()->create();

        // Attach all Artikel instances to the Category
        foreach ($artikels as $artikel) {
            $category->artikels()->attach($artikel);
        }
    }
}
