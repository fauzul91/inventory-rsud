<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PenerimaanStoreRequest;
use App\Http\Requests\V1\PenerimaanUpdateRequest;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;
use Exception;

class PenerimaanController extends Controller
{
    private PenerimaanService $penerimaanService;
    private BastService $bastService;

    public function __construct(PenerimaanService $penerimaanService, BastService $bastService)
    {
        $this->penerimaanService = $penerimaanService;
        $this->bastService = $bastService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanService->getPenerimaanList($filters, ['pending']);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function getAllCheckedPenerimaan(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanService->getPenerimaanList($filters, ['pending', 'checked'], 'check');
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function history(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanService->getPenerimaanList($filters, ['checked', 'confirmed', 'signed', 'paid']);
            return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function checkedHistory(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanService->getPenerimaanList($filters, ['confirmed', 'signed', 'paid'], 'check');
            return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function store(PenerimaanStoreRequest $request)
    {
        try {
            $data = $this->penerimaanService->create($request->validated());
            return ResponseHelper::jsonResponse(
                true,
                'Data penerimaan berhasil ditambahkan',
                $data,
                201
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->penerimaanService->getPenerimaanForEdit($id);
            return ResponseHelper::jsonResponse(
                true,
                'Data penerimaan berhasil diambil',
                $data,
                200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function update(PenerimaanUpdateRequest $request, string $id)
    {
        try {
            $data = $this->penerimaanService->updatePenerimaan($id, $request->validated());
            return ResponseHelper::jsonResponse(
                true,
                'Data penerimaan berhasil diperbarui',
                $data,
                200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->penerimaanService->delete($id);
            return ResponseHelper::jsonResponse(
                true,
                'Data penerimaan berhasil dihapus',
                null,
                200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateKelayakanBarang(Request $request, $penerimaanId, $detailId)
    {
        try {
            $validated = $request->validate([
                'is_layak' => ['required', 'boolean'],
            ]);

            $data = $this->penerimaanService
                ->updateKelayakanBarang($penerimaanId, $detailId, $validated);

            return ResponseHelper::jsonResponse(
                true,
                'Status kelayakan diperbarui',
                $data,
                200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function confirmPenerimaan(string $id)
    {
        try {
            $result = $this->penerimaanService->confirmPenerimaan($id);

            if ($result['success'] === false) {
                return ResponseHelper::jsonResponse(
                    false,
                    'Terjadi kesalahan: ' . $result['message'],
                    null,
                    422
                );
            }

            $bast = $this->bastService->generateBast($id);

            return ResponseHelper::jsonResponse(true, 'Status penerimaan berhasil dikonfirmasi & BAST berhasil dibuat', ['penerimaan' => $result['data'], 'bast' => $bast], 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function markDetailAsPaid($penerimaanId, $detailId)
    {
        try {
            $data = $this->penerimaanService->markDetailAsPaid($penerimaanId, $detailId);
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                return ResponseHelper::jsonResponse(
                    false,
                    $data['message'],
                    null,
                    404
                );
            }
            return ResponseHelper::jsonResponse(true, 'Barang berhasil dibayar', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}