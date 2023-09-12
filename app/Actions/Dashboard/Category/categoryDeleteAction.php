<?php

namespace App\Actions\Dashboard\Category;

class categoryDeleteAction
{
    public function execute($category)
    {
        $category->delete();
    }
}
