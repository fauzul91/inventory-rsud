<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PemesananStoreRequest;
use App\Http\Requests\V1\UpdateQuantityPenanggungJawabRequest;
use App\Services\V1\PemesananService;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    private PemesananService $pemesananService;

    public function __construct(PemesananService $pemesananService)
    {
        $this->pemesananService = $pemesananService;
    }
    private function getFilters(Request $request, array $extraFields = []): array
    {
        return $request->only(array_merge(['per_page', 'search'], $extraFields));
    }
    public function getAllStatusPemesananInstalasi(Request $request)
    {
        $filters = $this->getFilters($request, ['category']);

        $data = $this->pemesananService->getAllPemesanan($filters, ['pending', 'approved_pj', 'approved_admin_gudang']);
        return ResponseHelper::jsonResponse(true, 'Data status pemesanan berhasil diambil', $data, 200);
    }

    public function getAllStockPemesanan(Request $request)
    {
        $data = $this->pemesananService->getAllStoks($this->getFilters($request, ['category']));
        return ResponseHelper::jsonResponse(true, 'Data stok pemesanan berhasil diambil', $data, 200);
    }

    public function store(PemesananStoreRequest $request)
    {
        $data = $this->pemesananService->create($request->validated());
        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil ditambahkan', $data, 201);
    }

    public function show(string $id)
    {
        $data = $this->pemesananService->findById($id);
        if (!$data) {
            return ResponseHelper::jsonResponse(false, 'Data tidak ditemukan', null, 404);
        }

        return ResponseHelper::jsonResponse(true, 'Detail pemesanan berhasil diambil', $data, 200);
    }
}