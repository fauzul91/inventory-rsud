<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SatuanStoreRequest;
use App\Http\Requests\V1\SatuanUpdateRequest;
use App\Interfaces\V1\SatuanRepositoryInterface;
use App\Repositories\V1\SatuanRepository;
use Exception;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private SatuanRepository $satuanRepository;

    public function __construct(SatuanRepositoryInterface $satuanRepository)
    {
        $this->satuanRepository = $satuanRepository;
    }    
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $categories = $this->satuanRepository->getAllSatuan($filters);
            return ResponseHelper::jsonResponse(true, 'Data satuan berhasil diambil', $categories, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SatuanStoreRequest $request)
    {
        try {
            $satuan = $this->satuanRepository->create($request->validated());
            return ResponseHelper::jsonResponse(true, 'Data satuan berhasil ditambahkan', $satuan, 201);
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
            $satuan = $this->satuanRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Detail satuan berhasil diambil', $satuan, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SatuanUpdateRequest $request, string $id)
    {
        try {
            $satuan = $this->satuanRepository->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Data satuan berhasil diperbarui', $satuan, 200);
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
            $satuan = $this->satuanRepository->delete($id);
            return ResponseHelper::jsonResponse(true, 'Data satuan berhasil dihapus', $satuan, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}