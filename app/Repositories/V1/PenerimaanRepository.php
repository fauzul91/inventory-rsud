<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;

class PenerimaanRepository implements PenerimaanRepositoryInterface
{
    public function getAllPenerimaan(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }
    public function getAllCheckedPenerimaan(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->where('status', ['pending', 'checked'])
            ->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function getHistoryPenerimaan(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang', 'bast'])
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function findById($id)
    {
        return Penerimaan::with([
            'detailBarang.stok.category',
            'detailBarang.stok.satuan',
            'detailPegawai.pegawai.jabatan',
            'category'
        ])->findOrFail($id);
    }

    public function findWithDetails($id)
    {
        return Penerimaan::with('detailBarang')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Penerimaan::create($data);
    }

    public function update(Penerimaan $penerimaan, array $data)
    {
        $penerimaan->update($data);
        return $penerimaan->fresh();
    }

    public function delete(Penerimaan $penerimaan)
    {
        return $penerimaan->delete();
    }

    public function hasUnassessedItems($penerimaanId)
    {
        return DetailPenerimaanBarang::where('penerimaan_id', $penerimaanId)
            ->whereNull('quantity_layak')
            ->exists();
    }
    public function hasUnverifiedItems($penerimaanId)
    {
        return DetailPenerimaanBarang::where('penerimaan_id', $penerimaanId)
            ->whereRaw('quantity != quantity_layak')
            ->exists();
    }

    public function getUnassessedCount($penerimaanId)
    {
        return DetailPenerimaanBarang::where('penerimaan_id', $penerimaanId)
            ->whereNull('quantity_layak')
            ->count();
    }

    public function createDetailBarang(array $data)
    {
        return DetailPenerimaanBarang::create($data);
    }

    public function updateDetailBarang(DetailPenerimaanBarang $detail, array $data)
    {
        $detail->update($data);
        return $detail->fresh();
    }

    public function deleteDetailBarang(array $ids)
    {
        return DetailPenerimaanBarang::whereIn('id', $ids)->delete();
    }

    public function findDetailBarang($penerimaanId, $detailId)
    {
        return DetailPenerimaanBarang::where('id', $detailId)
            ->where('penerimaan_id', $penerimaanId)
            ->first();
    }

    public function createDetailPegawai(array $data)
    {
        return DetailPenerimaanPegawai::create($data);
    }

    public function updateDetailPegawai(DetailPenerimaanPegawai $detail, array $data)
    {
        $detail->update($data);
        return $detail;
    }

    public function findDetailPegawaiByPegawaiId($penerimaanId, $pegawaiId)
    {
        return DetailPenerimaanPegawai::where('penerimaan_id', $penerimaanId)
            ->where('pegawai_id', $pegawaiId)
            ->first();
    }
    public function updateDetailBarangPayment(DetailPenerimaanBarang $detail)
    {
        $detail->update(['is_paid' => true]);
        return $detail->fresh(['stok', 'penerimaan']);
    }
    public function getAllDetailBarang($penerimaanId)
    {
        return DetailPenerimaanBarang::where('penerimaan_id', $penerimaanId)->get();
    }
    public function updatePenerimaanStatus($penerimaanId, $status)
    {
        return Penerimaan::where('id', $penerimaanId)->update([
            'status' => $status
        ]);
    }
}