<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateQuantityPenanggungJawabRequest;
use App\Services\V1\PemesananService;
use Illuminate\Http\Request;

class PemesananApprovalController extends Controller
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
    public function updateQuantityPenanggungJawab(UpdateQuantityPenanggungJawabRequest $request, int $pemesananId)
    {
        $data = $this->pemesananService->updateQuantityPenanggungJawab($pemesananId, $request->validated()['details']);
        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diupdate', $data, 200);
    }
    public function getAllPendingPemesanan(Request $request)
    {
        $data = $this->pemesananService->getAllPemesanan($this->getFilters($request), ['pending']);
        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data, 200);
    }
    public function getAllPJRiwayatPemesanan(Request $request)
    {
        $data = $this->pemesananService->getAllPemesanan(
            $this->getFilters($request),
            ['approved_pj', 'approved_admin_gudang'],
            'pj'
        );
        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data, 200);
    }

    public function getAllPemesananApprovedPJ(Request $request)
    {
        $data = $this->pemesananService->getAllPemesanan($this->getFilters($request), ['approved_pj']);
        return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data, 200);
    }
}
