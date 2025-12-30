<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\V1\BastRepository;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class BastController
 * Menangani alur kerja Berita Acara Serah Terima (BAST), 
 * termasuk pengambilan list, download, dan upload file.
 * * @package App\Http\Controllers\Api\V1
 */
class BastController extends Controller
{
    private PenerimaanService $penerimaanService;
    private BastRepository $bastRepository;
    private BastService $bastService;

    /**
     * BastController constructor.
     * * @param BastService $bastService
     * @param BastRepository $bastRepository
     * @param PenerimaanService $penerimaanService
     */
    public function __construct(
        BastService $bastService,
        BastRepository $bastRepository,
        PenerimaanService $penerimaanService
    ) {
        $this->bastRepository = $bastRepository;
        $this->bastService = $bastService;
        $this->penerimaanService = $penerimaanService;
    }

    /**
     * Helper untuk mengambil filter dasar dari request.
     * * @param Request $request
     * @param array $extra Parameter tambahan selain per_page dan search
     * @return array
     */
    private function getFilters(Request $request, array $extra = []): array
    {
        return $request->only(array_merge(['per_page', 'search'], $extra));
    }

    /**
     * Mengambil daftar BAST yang belum ditandatangani.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getUnsignedBast(Request $request): JsonResponse
    {
        $data = $this->bastService->getBastList($this->getFilters($request), 'unsigned');
        return ResponseHelper::jsonResponse(true, 'Data Unsigned BAST berhasil diambil', $data, 200);
    }

    /**
     * Mengambil daftar BAST yang sudah ditandatangani.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getSignedBast(Request $request): JsonResponse
    {
        $data = $this->bastService->getBastList($this->getFilters($request), 'signed');
        return ResponseHelper::jsonResponse(true, 'Data Signed BAST berhasil diambil', $data, 200);
    }

    /**
     * Mengambil daftar pembayaran BAST berdasarkan status (paid/unpaid).
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllPaymentBast(Request $request): JsonResponse
    {
        $statusMap = [
            'unpaid' => ['signed'],
            'paid' => ['paid'],
        ];

        $statuses = $statusMap[$request->query('status')] ?? ['signed', 'paid'];

        $data = $this->penerimaanService->getPenerimaanList(
            $this->getFilters($request, ['category']),
            $statuses,
            'paid'
        );

        return ResponseHelper::jsonResponse(true, 'Data bast berhasil diambil', $data, 200);
    }

    /**
     * Download file BAST yang belum ditandatangani.
     * * @param mixed $bastId
     * @return mixed
     */
    public function downloadUnsignedBast($bastId)
    {
        return $this->bastService->downloadBastFile(
            $this->bastRepository->findBast($bastId),
            'unsigned'
        );
    }

    /**
     * Download file BAST yang sudah ditandatangani.
     * * @param mixed $bastId
     * @return mixed
     */
    public function downloadSignedBast($bastId)
    {
        return $this->bastService->downloadBastFile(
            $this->bastRepository->findBast($bastId),
            'signed'
        );
    }

    /**
     * Mengupload file BAST yang sudah ditandatangani oleh user.
     * * @param Request $request
     * @param mixed $penerimaanId
     * @return JsonResponse
     */
    public function upload(Request $request, $penerimaanId): JsonResponse
    {
        $request->validate([
            'uploaded_signed_file' => 'required|file|mimes:pdf|max:4096',
        ]);

        $result = $this->bastService->uploadSignedBast($penerimaanId, $request->file('uploaded_signed_file'));

        return ResponseHelper::jsonResponse(true, 'BAST bertandatangan berhasil diupload', $result, 200);
    }

    /**
     * Mengambil riwayat log penggunaan atau perubahan BAST.
     * * @param Request $request
     * @return JsonResponse
     */
    public function historyBast(Request $request): JsonResponse
    {
        $history = $this->bastService->history($this->getFilters($request, ['sort_by']));
        return ResponseHelper::jsonResponse(true, 'Data riwayat BAST berhasil diambil', $history, 200);
    }
}