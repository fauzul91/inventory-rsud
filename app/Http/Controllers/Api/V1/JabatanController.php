<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategoryStoreRequest;
use App\Http\Requests\V1\CategoryUpdateRequest;
use App\Interfaces\V1\JabatanRepositoryInterface;
use App\Repositories\V1\JabatanRepository;
use Exception;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private JabatanRepository $jabatanRepository;

    public function __construct(JabatanRepositoryInterface $jabatanRepository)
    {
        $this->jabatanRepository = $jabatanRepository;
    }
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->query('search'),
                'per_page' => $request->query('per_page'),
                'limit' => $request->query('limit'),
                'sort_by' => $request->query('sort_by'),
            ];

            $categories = $this->jabatanRepository->getAllJabatan($filters);
            return ResponseHelper::jsonResponse(true, 'Data kategori berhasil diambil', $categories, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        try {
            $category = $this->jabatanRepository->create($request->validated());
            return ResponseHelper::jsonResponse(true, 'Data kategori berhasil ditambahkan', $category, 201);
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
            $category = $this->jabatanRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Detail kategori berhasil diambil', $category, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, string $id)
    {
        try {
            $category = $this->jabatanRepository->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Data kategori berhasil diperbarui', $category, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = $this->jabatanRepository->delete($id);
            return ResponseHelper::jsonResponse(true, 'Data kategori berhasil dihapus', $category, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}