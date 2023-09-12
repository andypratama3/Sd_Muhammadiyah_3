<?php
namespace App\Actions\Dashboard\Category;

use App\Models\Category;
use App\DataTransferObjects\CategoryData;

class CategoryAction
{
    public function execute(CategoryData $categoryData)
    {
        $category = Category::updateOrCreate(
            ['slug' => $categoryData->slug],
            [
                'name' => $categoryData->name,
            ]);
    }
}
