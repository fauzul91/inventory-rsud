<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\BastRepositoryInterface;
use App\Models\Bast;
use App\Models\Penerimaan;

class BastRepository implements BastRepositoryInterface
{
    public function getBastList(array $filters, array $statuses)
    {
        $query = Penerimaan::with([
            'category:id,name',
            'detailPegawai.pegawai:id,name',
            'detailBarang',
            'bast'
        ])->whereIn('status', $statuses);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('no_surat', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($qc) use ($search) {
                        $qc->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('created_at', 'asc');
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
        ])
            ->whereHas('penerimaan', function ($q) {
                $q->whereIn('status', ['signed', 'paid']);
            })
            ->orderBy('uploaded_at', 'desc');

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
