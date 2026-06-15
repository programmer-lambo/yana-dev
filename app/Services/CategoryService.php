<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Note;

class CategoryService
{
    public function getAllCategories()
    {
        // Mengambil semua kategori beserta hitungan berapa jumlah notes di dalamnya
        return Category::withCount('notes')->get();
    }

    public function getNotesByCategoryId(int $categoryId)
    {
        // Pastikan kategorinya emang ada dulu di YANA.dev
        $categoryExists = Category::where('id', "=", $categoryId)->exists();
        
        if (!$categoryExists) {
            throw new \Exception("Kategori tidak ditemukan.", 404);
        }

        // Ambil semua notes yang statusnya searchable berdasarkan kategori ID
        return Note::with(['author:id,name', 'category:id,name'])
            ->where('category_id', $categoryId)
            ->where('is_indexed', true)
            ->latest()
            ->get();
    }
}