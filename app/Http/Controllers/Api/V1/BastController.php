<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\BastRepositoryInterface;
use App\Repositories\V1\BastRepository;
use Exception;
use Illuminate\Http\Request;

class BastController extends Controller
{
    private BastRepository $bastRepository;

    public function __construct(BastRepositoryInterface $bastRepository)
    {
        $this->bastRepository = $bastRepository;
    }
    public function getUnsignedBast(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->bastRepository->getUnsignedBast($filters);
            $transformed = $data->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_surat' => $item->no_surat,
                    'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                    'category_name' => $item->category->name ?? null,
                    'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                    'status' => 'Belum Ditandatangani',
                    'bast' => $item->status === 'confirmed' && $item->bast ? [
                        'id' => $item->bast->id,
                        'file_url' => asset('storage/' . $item->bast->filename),
                        'download_endpoint' => route('bast.unsigned.download', ['id' => $item->bast->id]),
                    ] : null,
                ];
            });

            $data->setCollection($transformed);
            return ResponseHelper::jsonResponse(true, 'Data Unsigned BAST berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function getSignedBast(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->bastRepository->getSignedBast($filters);
            $transformed = $data->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_surat' => $item->no_surat,
                    'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                    'category_name' => $item->category->name ?? null,
                    'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                    'status' => $item->status === 'signed' ? 'Telah Ditandatangani' : 'Belum Ditandatangani',
                    'bast' => $item->bast ? [
                        'id' => $item->bast->id,
                        'signed_file_url' => $item->bast->uploaded_signed_file
                            ? asset('storage/' . $item->bast->uploaded_signed_file)
                            : null,
                        'download_endpoint' => route('bast.signed.download', ['id' => $item->bast->id]),
                    ] : null,
                ];
            });

            $data->setCollection($transformed);
            return ResponseHelper::jsonResponse(true, 'Data Signed BAST berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    /**
     * Generate BAST PDF
     */
    public function generate($penerimaanId)
    {
        try {
            $result = $this->bastRepository->generateBast($penerimaanId);
            return ResponseHelper::jsonResponse(true, 'Dokumen BAST berhasil dibuat', $result, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function downloadUnsignedBast($bastId)
    {
        try {
            return $this->bastRepository->downloadUnsignedBast($bastId);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function downloadSignedBast($bastId)
    {
        try {
            return $this->bastRepository->downloadSignedBast($bastId);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Upload BAST setelah ditandatangani
     */
    public function upload(Request $request, $penerimaanId)
    {
        try {
            $request->validate([
                'uploaded_signed_file' => 'required|file|mimes:pdf|max:4096',
            ]);

            $file = $request->file('uploaded_signed_file');
            $result = $this->bastRepository->uploadBast($penerimaanId, $file);

            return ResponseHelper::jsonResponse(true, 'BAST bertandatangan berhasil diupload', $result, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Ambil riwayat BAST milik user yang login
     */
    public function historyBast(Request $request)
    {
        try {
            $filters = $request->only(['sort_by', 'per_page']);
            $history = $this->bastRepository->historyBast($filters);

            return ResponseHelper::jsonResponse(true,'Data riwayat BAST berhasil diambil',$history,200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }
}
