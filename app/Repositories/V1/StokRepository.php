<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\StokRepositoryInterface;
use App\Models\DetailPenerimaanBarang;
use App\Models\Monitoring;
use App\Models\Penerimaan;
use App\Models\Stok;
use App\Models\StokHistory;

class StokRepository implements StokRepositoryInterface
{
    public function getAllStoksForSelect($categoryId = null)
    {
        $query = Stok::with('satuan:id,name')
            ->select('id', 'name', 'satuan_id');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy('name', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'satuan_id' => $item->satuan_id,
                    'satuan_name' => $item->satuan->name ?? null,
                ];
            });
    }
    public function getAllYearForSelect()
    {
        return Penerimaan::query()
            ->whereIn('status', ['checked', 'confirmed', 'signed', 'paid'])
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year')
            ->get()
            ->map(fn($row) => [
                'label' => (string) $row->year,
                'value' => $row->year,
            ]);
    }
    public function getAllStoks($filters)
    {
        $query = Stok::query()->with('satuan');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        return $query;
    }
    public function getStokById($id)
    {
        $stok = Stok::with([
            'category:id,name',
            'satuan:id,name',
            'detailPenerimaanBarang' => function ($query) {
                $query->where('is_layak', true)
                    ->whereHas('penerimaan', function ($q) {
                        $q->whereIn('status', ['checked', 'confirmed', 'signed', 'paid']);
                    })
                    ->join('penerimaans', 'detail_penerimaan_barangs.penerimaan_id', '=', 'penerimaans.id')
                    ->orderBy('penerimaans.created_at', 'asc')
                    ->select('detail_penerimaan_barangs.*');
            }
        ])->findOrFail($id);

        return $stok;
    }
    public function getPaidBastStock($filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->where('status', 'paid');

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('no_surat', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
    public function getUnpaidBastStock($filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->where('status', 'signed');

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('no_surat', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
    public function edit($id)
    {
        return Stok::where('id', $id)
            ->select(['name', 'minimum_stok'])
            ->firstOrFail();
    }
    public function update(array $data, $id)
    {
        $stok = Stok::findOrFail($id);
        $allowedData = collect($data)->only(['name', 'minimum_stok'])->toArray();
        $stok->update($allowedData);

        return $stok;
    }
}