<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;

class PenerimaanRepository implements PenerimaanRepositoryInterface
{
    public function getPenerimaanForTable(array $filters = [], array $statuses = null)
    {
        $query = Penerimaan::with([
            'category:id,name',
            'user.roles:id,name',
            'detailPegawai.pegawai:id,name'
        ])->select('id', 'no_surat', 'category_id', 'user_id', 'status');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('no_surat', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }
    public function findById($id)
    {
        return Penerimaan::with([
            'category:id,name',
            'detailBarang.stok' => function ($q) {
                $q->select('id', 'name', 'category_id', 'satuan_id')
                    ->with([
                        'category:id,name',
                        'satuan:id,name'
                    ]);
            },
            'detailPegawai.pegawai' => function ($q) {
                $q->select('id', 'name', 'nip', 'jabatan_id')
                    ->with([
                        'jabatan:id,name'
                    ]);
            }
        ])->select('id', 'no_surat', 'deskripsi', 'status', 'category_id')
            ->findOrFail($id);
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
    public function hasUnfitItems($penerimaanId)
    {
        return DetailPenerimaanBarang::where('penerimaan_id', $penerimaanId)
            ->where('is_layak', false)
            ->exists();
    }
    public function getUnassessedCount($penerimaanId)
    {
        return DetailPenerimaanBarang::where('penerimaan_id', $penerimaanId)
            ->whereNull('is_layak')
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
    public function getDetailPegawaisByPenerimaanId($penerimaanId)
    {
        return DetailPenerimaanPegawai::where('penerimaan_id', $penerimaanId)->get();
    }

    public function findDetailPegawaiByPegawaiId($penerimaanId, $pegawaiId)
    {
        return DetailPenerimaanPegawai::where('penerimaan_id', $penerimaanId)
            ->where('pegawai_id', $pegawaiId)
            ->first();
    }
    public function createDetailPegawai(array $data)
    {
        return DetailPenerimaanPegawai::create($data);
    }

    public function updateDetailPegawai(DetailPenerimaanPegawai $detailPegawai, array $data)
    {
        $detailPegawai->update($data);
        return $detailPegawai->fresh();
    }

    public function deleteDetailPegawai(DetailPenerimaanPegawai $detailPegawai)
    {
        return $detailPegawai->delete();
    }

}