<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\BastRepositoryInterface;
use App\Models\Bast;
use App\Models\Penerimaan;

class BastRepository implements BastRepositoryInterface
{
    public function getUnsignedBast(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang', 'bast'])
            ->where('status', 'confirmed');

        if (!empty($filters['sort_by'])) {
            $query->orderBy('created_at', $filters['sort_by'] === 'oldest' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function getSignedBast(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->whereIn('status', ['signed', 'paid']);

        if (!empty($filters['sort_by'])) {
            $query->orderBy('created_at', $filters['sort_by'] === 'oldest' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function findPenerimaan($id)
    {
        return Penerimaan::with(['detailBarang', 'detailPegawai.pegawai'])->findOrFail($id);
    }

    public function createBast($penerimaanId, $filename)
    {
        return Bast::create([
            'penerimaan_id' => $penerimaanId,
            'filename' => $filename,
        ]);
    }

    public function findBast($id)
    {
        return Bast::findOrFail($id);
    }

    public function updateSignedBast($bast, $path)
    {
        $bast->update([
            'uploaded_signed_file' => $path,
            'uploaded_at' => now(),
        ]);

        return $bast;
    }

    public function getHistory(array $filters)
    {
        $query = Bast::with([
            'penerimaan.category',
            'penerimaan.detailPegawai.pegawai',
            'penerimaan.detailBarang'
        ])->orderBy('uploaded_at', 'desc');

        if (!empty($filters['sort_by'])) {
            $query->orderBy('uploaded_at', $filters['sort_by'] === 'oldest' ? 'asc' : 'desc');
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }
    public function findBastByPenerimaanId($penerimaanId)
    {
        return Bast::where('penerimaan_id', $penerimaanId)->first();
    }
}
