<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\StokRepositoryInterface;
use App\Models\Monitoring;
use App\Models\Penerimaan;
use App\Models\Stok;
use App\Models\StokHistory;

class StokRepository implements StokRepositoryInterface
{    
    public function getAllStoksForSelect($categoryId = null)
    {
        $query = Stok::with('satuan:id,name')
            ->select('id', 'name', 'satuan_id', 'price');

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
                    'price' => $item->price,
                ];
            });
    }
    public function getAllYearForSelect()
    {
        return StokHistory::select('year')->distinct()->orderBy('year', 'asc')->get();
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
            ->where('status', 'confirmed');

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('no_surat', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
}