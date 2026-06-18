<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories()
    {
        // Mengambil semua kategori beserta hitungan berapa jumlah notes di dalamnya
        return Category::withCount('notes')->get();
    }
}