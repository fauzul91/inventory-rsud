<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\V1\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class CategoryController
 * Mengelola data kategori barang untuk keperluan inventory dan pemesanan.
 * * @package App\Http\Controllers\Api\V1
 */
class CategoryController extends Controller
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * CategoryController constructor.
     * * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Mengambil semua kategori untuk keperluan dropdown/select.
     * * @return JsonResponse
     */
    public function getAllForSelect(): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Data kategori berhasil diambil',
            $this->categoryRepository->getAllCategoriesForSelect()
        );
    }

    /**
     * Menampilkan daftar kategori dengan paginasi dan pengurutan.
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Langsung ambil parameter yang dibutuhkan saja (mengurangi volume)
        $filters = $request->only(['per_page', 'sort_by']);

        $categories = $this->categoryRepository->getAllCategories($filters);

        return ResponseHelper::jsonResponse(true, 'Data kategori berhasil diambil', $categories);
    }

    /**
     * Menampilkan detail kategori berdasarkan ID.
     * * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $category = $this->categoryRepository->edit($id);

        return ResponseHelper::jsonResponse(true, 'Detail kategori berhasil diambil', $category);
    }
}