<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PenerimaanStoreRequest;
use App\Http\Requests\V1\PenerimaanUpdateRequest;
use App\Interfaces\V1\BastRepositoryInterface;
use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Repositories\V1\BastRepository;
use App\Repositories\V1\PenerimaanRepository;
use Illuminate\Http\Request;
use Exception;

class PenerimaanController extends Controller
{
    private PenerimaanRepository $penerimaanRepository;
    private BastRepository $bastRepository;

    public function __construct(PenerimaanRepositoryInterface $penerimaanRepository, BastRepositoryInterface $bastRepository)
    {
        $this->penerimaanRepository = $penerimaanRepository;
        $this->bastRepository = $bastRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->penerimaanRepository->getAllPenerimaan($filters);
            $transformed = $data->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_surat' => $item->no_surat,
                    'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                    'category_name' => $item->category->name ?? null,
                    'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                    'status' => $item->status === 'pending' ? 'Belum Dikonfirmasi' : 'Telah Dikonfirmasi',
                ];
            });

            $data->setCollection($transformed);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(PenerimaanStoreRequest $request)
    {
        try {
            $data = $this->penerimaanRepository->create($request->validated());
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil ditambahkan', $data, 201);
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
            $data = $this->penerimaanRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PenerimaanUpdateRequest $request, string $id)
    {
        try {
            $data = $this->penerimaanRepository->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diperbarui', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->penerimaanRepository->delete($id);
            return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update kelayakan barang.
     */
    public function markBarangLayak(Request $request, $penerimaanId, $detailId)
    {
        try {
            $request->validate([
                'is_layak' => 'required|boolean',
            ]);

            $result = $this->penerimaanRepository
                ->markBarangLayak($penerimaanId, $detailId, $request->is_layak);

            if ($result['success'] === false) {
                return ResponseHelper::jsonResponse(false, $result['message'], null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Status kelayakan diperbarui', $result['data'], 200);

        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }


    /**
     * Konfirmasi penerimaan (ubah status ke confirmed).
     */
    public function confirmPenerimaan(string $id)
    {
        try {
            $result = $this->penerimaanRepository->confirmPenerimaan($id);

            if ($result['success'] === false) {
                return ResponseHelper::jsonResponse(
                    false,
                    'Terjadi kesalahan: ' . $result['message'],
                    null,
                    422
                );
            }
            $bast = $this->bastRepository->generateBast($id);
            return ResponseHelper::jsonResponse(
                true,
                'Status penerimaan berhasil dikonfirmasi & BAST berhasil dibuat',
                [
                    'penerimaan' => $result['data'],
                    'bast' => $bast
                ],
                200
            );

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

            $data = $this->penerimaanRepository->getHistoryPenerimaan($filters);
            $transformed = $data->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_surat' => $item->no_surat,
                    'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                    'category_name' => $item->category->name ?? null,
                    'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                    'status' => 'Telah Dikonfirmasi',
                ];
            });

            $data->setCollection($transformed);
            return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
