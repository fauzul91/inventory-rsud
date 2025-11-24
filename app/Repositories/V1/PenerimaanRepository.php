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
            ->where('status', 'pending');

        $query = $this->applySorting($query, $filters);
        
        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function getHistoryPenerimaan(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang', 'bast'])
            ->where('status', 'confirmed');

        $query = $this->applySorting($query, $filters);
        
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
            ->whereNull('is_layak')
            ->exists();
    }

    // Detail Barang Methods
    public function createDetailBarang(array $data)
    {
        return DetailPenerimaanBarang::create($data);
    }

    public function updateDetailBarang(DetailPenerimaanBarang $detail, array $data)
    {
        $detail->update($data);
        return $detail;
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

    // Detail Pegawai Methods
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

    // Helper Methods
    private function applySorting($query, array $filters)
    {
        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}