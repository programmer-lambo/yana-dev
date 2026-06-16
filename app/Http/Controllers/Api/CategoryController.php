<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;


class CategoryController extends Controller
{
    protected $categoryService;

    // Inject service kategori lewat constructor
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // 1. Ambil semua daftar kategori
    public function index()
    {
        try {
            $categories = $this->categoryService->getAllCategories();
            return response()->json([
                'success' => true,
                'message' => 'Daftar kategori berhasil dimuat.',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Ambil daftar artikel berdasarkan ID Kategori
    public function showNotes($id)
    {
        try {
            $notes = $this->categoryService->getNotesByCategoryId((int) $id);
            return response()->json([
                'success' => true,
                'message' => 'Daftar catatan berdasarkan kategori berhasil ditemukan.',
                'data' => $notes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}