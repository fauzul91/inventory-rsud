<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PenerimaanStoreRequest;
use App\Http\Requests\V1\PenerimaanUpdateRequest;
use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Repositories\V1\PenerimaanRepository;
use Illuminate\Http\Request;
use Exception;

class PenerimaanController extends Controller
{
    private PenerimaanRepository $penerimaanRepository;

    public function __construct(PenerimaanRepositoryInterface $penerimaanRepository)
    {
        $this->penerimaanRepository = $penerimaanRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->query('search'),
                'per_page' => $request->query('per_page'),
                'limit' => $request->query('limit'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanRepository->getAllPenerimaan($filters);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(PenerimaanStoreRequest $request)
    {
        try {
            $data = $this->penerimaanRepository->create($request->validated());
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil ditambahkan', $data, 201);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->penerimaanRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PenerimaanUpdateRequest $request, string $id)
    {
        try {
            $data = $this->penerimaanRepository->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diperbarui', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->penerimaanRepository->delete($id);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update kelayakan barang.
     */
    public function setLayak(Request $request, string $detailId)
    {
        try {
            $request->validate([
                'is_layak' => 'required|boolean',
            ]);

            $data = $this->penerimaanRepository->setLayak($detailId, $request->is_layak);
            return ResponseHelper::jsonResponse(true, 'Status kelayakan barang berhasil diperbarui', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Konfirmasi penerimaan (ubah status ke confirmed).
     */
    public function confirm(string $id)
    {
        try {
            $data = $this->penerimaanRepository->updateConfirmationStatus($id);
            return ResponseHelper::jsonResponse(true, 'Status penerimaan berhasil dikonfirmasi', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
