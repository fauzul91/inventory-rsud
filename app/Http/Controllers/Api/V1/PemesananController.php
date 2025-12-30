<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PemesananStoreRequest;
use App\Services\V1\PemesananService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class PemesananController
 * Mengelola siklus hidup pemesanan barang oleh unit instalasi, 
 * mulai dari pengecekan ketersediaan stok hingga pemantauan status persetujuan.
 */
class PemesananController extends Controller
{
    /**
     * @var PemesananService
     */
    private PemesananService $pemesananService;

    /**
     * PemesananController constructor.
     * * @param PemesananService $pemesananService
     */
    public function __construct(PemesananService $pemesananService)
    {
        $this->pemesananService = $pemesananService;
    }

    /**
     * Helper internal untuk mengekstrak filter dari request.
     * * @param Request $request
     * @param array $extraFields
     * @return array
     */
    private function getFilters(Request $request, array $extraFields = []): array
    {
        return $request->only(array_merge(['per_page', 'search'], $extraFields));
    }

    /**
     * Mengambil daftar status pemesanan untuk unit instalasi.
     * Mencakup status pending, disetujui PJ, hingga disetujui Admin Gudang.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllStatusPemesananInstalasi(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request, ['category']);
        $statuses = ['pending', 'approved_pj', 'approved_admin_gudang'];

        $data = $this->pemesananService->getAllPemesanan($filters, $statuses);
        
        return ResponseHelper::jsonResponse(true, 'Data status pemesanan berhasil diambil', $data);
    }

    /**
     * Mengambil ketersediaan stok barang yang siap untuk dipesan.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllStockPemesanan(Request $request): JsonResponse
    {
        $data = $this->pemesananService->getAllStoks($this->getFilters($request, ['category']));
        
        return ResponseHelper::jsonResponse(true, 'Data stok pemesanan berhasil diambil', $data);
    }

    /**
     * Membuat pengajuan pemesanan barang baru.
     * * @param PemesananStoreRequest $request
     * @return JsonResponse
     */
    public function store(PemesananStoreRequest $request): JsonResponse
    {
        $data = $this->pemesananService->create($request->validated());
        
        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil ditambahkan', $data, 201);
    }

    /**
     * Mengambil informasi detail pemesanan berdasarkan ID dokumen.
     * * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $data = $this->pemesananService->findById($id);
        
        if (!$data) {
            return ResponseHelper::jsonResponse(false, 'Data tidak ditemukan', null, 404);
        }

        return ResponseHelper::jsonResponse(true, 'Detail pemesanan berhasil diambil', $data);
    }
}