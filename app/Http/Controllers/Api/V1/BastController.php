<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\V1\BastRepository;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;

class BastController extends Controller
{
    private PenerimaanService $penerimaanService;
    private BastRepository $bastRepository;
    private BastService $bastService;

    public function __construct(
        BastService $bastService,
        BastRepository $bastRepository,
        PenerimaanService $penerimaanService
    ) {
        $this->bastRepository = $bastRepository;
        $this->bastService = $bastService;
        $this->penerimaanService = $penerimaanService;
    }
    private function getFilters(Request $request, array $extra = []): array
    {
        return $request->only(array_merge(['per_page', 'search'], $extra));
    }

    public function getUnsignedBast(Request $request)
    {
        $data = $this->bastService->getBastList($this->getFilters($request), 'unsigned');
        return ResponseHelper::jsonResponse(true, 'Data Unsigned BAST berhasil diambil', $data, 200);
    }

    public function getSignedBast(Request $request)
    {
        $data = $this->bastService->getBastList($this->getFilters($request), 'signed');
        return ResponseHelper::jsonResponse(true, 'Data Signed BAST berhasil diambil', $data, 200);
    }

    public function getAllPaymentBast(Request $request)
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

    public function downloadUnsignedBast($bastId)
    {
        return $this->bastService->downloadBastFile(
            $this->bastRepository->findBast($bastId),
            'unsigned'
        );
    }

    public function downloadSignedBast($bastId)
    {
        return $this->bastService->downloadBastFile(
            $this->bastRepository->findBast($bastId),
            'signed'
        );
    }

    public function upload(Request $request, $penerimaanId)
    {
        // Validasi tetap perlu, tapi isinya kita ringkas
        $request->validate([
            'uploaded_signed_file' => 'required|file|mimes:pdf|max:4096',
        ]);

        $result = $this->bastService->uploadSignedBast($penerimaanId, $request->file('uploaded_signed_file'));

        return ResponseHelper::jsonResponse(true, 'BAST bertandatangan berhasil diupload', $result, 200);
    }

    public function historyBast(Request $request)
    {
        $history = $this->bastService->history($this->getFilters($request, ['sort_by']));
        return ResponseHelper::jsonResponse(true, 'Data riwayat BAST berhasil diambil', $history, 200);
    }
}