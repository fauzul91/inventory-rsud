<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateQuantityPenanggungJawabRequest;
use App\Services\V1\PemesananService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class PemesananApprovalController
 * Menangani alur persetujuan (approval) pemesanan barang oleh Penanggung Jawab dan Admin Gudang.
 * * @package App\Http\Controllers\Api\V1
 */
class PemesananApprovalController extends Controller
{
    /**
     * @var PemesananService
     */
    private PemesananService $pemesananService;

    /**
     * PemesananApprovalController constructor.
     * * @param PemesananService $pemesananService
     */
    public function __construct(PemesananService $pemesananService)
    {
        $this->pemesananService = $pemesananService;
    }

    /**
     * Helper untuk mengekstrak filter pencarian dan paginasi dari request.
     * * @param Request $request
     * @param array $extraFields
     * @return array
     */
    private function getFilters(Request $request, array $extraFields = []): array
    {
        return $request->only(array_merge(['per_page', 'search'], $extraFields));
    }

    /**
     * Memperbarui kuantitas barang oleh Penanggung Jawab sebelum proses approval.
     * * @param UpdateQuantityPenanggungJawabRequest $request
     * @param int $pemesananId
     * @return JsonResponse
     */
    public function updateQuantityPenanggungJawab(UpdateQuantityPenanggungJawabRequest $request, int $pemesananId): JsonResponse
    {
        $data = $this->pemesananService->updateQuantityPenanggungJawab($pemesananId, $request->validated()['details']);

        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diupdate', $data);
    }

    /**
     * Mengambil semua daftar pemesanan yang masih berstatus pending.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllPendingPemesanan(Request $request): JsonResponse
    {
        $data = $this->pemesananService->getAllPemesanan($this->getFilters($request), ['pending']);

        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data);
    }

    /**
     * Mengambil riwayat pemesanan yang telah diproses oleh Penanggung Jawab.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllPJRiwayatPemesanan(Request $request): JsonResponse
    {
        $data = $this->pemesananService->getAllPemesanan(
            $this->getFilters($request),
            ['approved_pj', 'approved_admin_gudang', 'rejected_pj', 'rejected_admin_gudang'],
            'pj'
        );

        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data);
    }

    /**
     * Mengambil daftar pemesanan yang sudah disetujui oleh Penanggung Jawab.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllPemesananApprovedPJ(Request $request): JsonResponse
    {
        $data = $this->pemesananService->getAllPemesanan($this->getFilters($request), ['approved_pj']);

        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data);
    }
}