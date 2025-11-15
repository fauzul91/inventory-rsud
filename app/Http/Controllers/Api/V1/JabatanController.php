<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategoryStoreRequest;
use App\Http\Requests\V1\CategoryUpdateRequest;
use App\Http\Requests\V1\JabatanStoreRequest;
use App\Http\Requests\V1\JabatanUpdateRequest;
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
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $jabatans = $this->jabatanRepository->getAllJabatan($filters);
            return ResponseHelper::jsonResponse(true, 'Data jabatan berhasil diambil', $jabatans, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JabatanStoreRequest $request)
    {
        try {
            $jabatan = $this->jabatanRepository->create($request->validated());
            return ResponseHelper::jsonResponse(true, 'Data jabatan berhasil ditambahkan', $jabatan, 201);
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
            $jabatan = $this->jabatanRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Detail jabatan berhasil diambil', $jabatan, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JabatanUpdateRequest $request, string $id)
    {
        try {
            $category = $this->jabatanRepository->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Data jabatan berhasil diperbarui', $category, 200);
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
            return ResponseHelper::jsonResponse(true, 'Data jabatan berhasil dihapus', $category, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}