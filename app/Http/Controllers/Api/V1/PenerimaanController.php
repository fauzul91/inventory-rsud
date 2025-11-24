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

            $data = $this->penerimaanService->getAllPenerimaan($filters);
            $transformed = $this->transformPenerimaanList($data);

            return ResponseHelper::jsonResponse(true,'Data penerimaan berhasil diambil',$transformed,200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function store(PenerimaanStoreRequest $request)
    {
        try {
            $data = $this->penerimaanService->create($request->validated());
            return ResponseHelper::jsonResponse(true,'Data penerimaan berhasil ditambahkan',$data,201
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->penerimaanService->getPenerimaanForEdit($id);
            return ResponseHelper::jsonResponse(true,'Data penerimaan berhasil diambil',$data,200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function update(PenerimaanUpdateRequest $request, string $id)
    {
        try {
            $data = $this->penerimaanService->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true,'Data penerimaan berhasil diperbarui',$data,200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->penerimaanService->delete($id);
            return ResponseHelper::jsonResponse(true,'Data penerimaan berhasil dihapus',null,200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function markBarangLayak(Request $request, $penerimaanId, $detailId)
    {
        try {
            $request->validate([
                'is_layak' => 'required|boolean',
            ]);

            $result = $this->penerimaanService->markBarangLayak($penerimaanId,$detailId,$request->is_layak
            );

            if ($result['success'] === false) {
                return ResponseHelper::jsonResponse(false,$result['message'],null,404
                );
            }

            return ResponseHelper::jsonResponse(true,'Status kelayakan diperbarui',$result['data'],200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function confirmPenerimaan(string $id)
    {
        try {
            $result = $this->penerimaanService->confirmPenerimaan($id);

            if ($result['success'] === false) {
                return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $result['message'],null,422
                );
            }

            $bast = $this->bastService->generateBast($id);

            return ResponseHelper::jsonResponse(true,'Status penerimaan berhasil dikonfirmasi & BAST berhasil dibuat',['penerimaan' => $result['data'],'bast' => $bast],200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    public function history(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanService->getHistoryPenerimaan($filters);
            $transformed = $this->transformPenerimaanList($data, true);

            return ResponseHelper::jsonResponse(true,'History penerimaan berhasil diambil',$transformed,200
            );
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }

    private function transformPenerimaanList($data, $isHistory = false)
    {
        $transformed = $data->getCollection()->map(function ($item) use ($isHistory) {
            return [
                'id' => $item->id,
                'no_surat' => $item->no_surat,
                'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                'category_name' => $item->category->name ?? null,
                'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                'status' => $isHistory ? 'Telah Dikonfirmasi' : 
                    ($item->status === 'pending' ? 'Belum Dikonfirmasi' : 'Telah Dikonfirmasi'),
            ];
        });

        $data->setCollection($transformed);
        return $data;
    }
}