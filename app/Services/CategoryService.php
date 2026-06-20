<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories()
    {
        return Category::withCount('notes')->get();
    }
}