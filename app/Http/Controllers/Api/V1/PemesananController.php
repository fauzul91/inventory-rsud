<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PemesananStoreRequest;
use App\Http\Requests\V1\UpdateDetailQuantityRequest;
use App\Services\V1\PemesananService;
use Exception;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    private PemesananService $pemesananService;

    public function __construct(PemesananService $pemesananService)
    {
        $this->pemesananService = $pemesananService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'search' => $request->query('search'),
            ];

            $data = $this->pemesananService->getAllPemesanan($filters);
            return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function getAllStockPemesanan(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'category' => $request->query('category'),
                'search' => $request->query('search'),
            ];

            $data = $this->pemesananService->getAllStoks($filters);
            return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PemesananStoreRequest $request)
    {
        try {
            $data = $this->pemesananService->create($request->validated());
            return ResponseHelper::jsonResponse(
                true,
                'Data pemesanan berhasil ditambahkan',
                $data,
                201
            );
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
            $data = $this->pemesananService->findById($id);

            if (!$data) {
                return ResponseHelper::jsonResponse(false, 'Data tidak ditemukan', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Detail pemesanan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateDetailQuantity(UpdateDetailQuantityRequest $request, int $pemesananId, int $detailId)
    {
        try {
            $quantity = $request->validated()['quantity'];
            $data = $this->pemesananService->updateDetailQuantity($pemesananId, $detailId, $quantity);
            return ResponseHelper::jsonResponse(true, 'Quantity berhasil diperbarui', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
