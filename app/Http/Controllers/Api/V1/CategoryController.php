<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategoryStoreRequest;
use App\Http\Requests\V1\CategoryUpdateRequest;
use App\Interfaces\V1\CategoryRepositoryInterface;
use App\Repositories\V1\CategoryRepository;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }
    public function getAllForSelect()
    {
        try {
            $categories = $this->categoryRepository->getAllCategoriesForSelect();
            return ResponseHelper::jsonResponse(true, 'Data kategori berhasil diambil', $categories, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $categories = $this->categoryRepository->getAllCategories($filters);
            return ResponseHelper::jsonResponse(true, 'Data kategori berhasil diambil', $categories, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = $this->categoryRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Detail kategori berhasil diambil', $category, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}