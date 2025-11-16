<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\SatuanRepositoryInterface;
use App\Models\Satuan;
use App\Models\Stok;

class SatuanRepository implements SatuanRepositoryInterface
{
    public function getAllSatuan(array $filters)
    {
        $query = Satuan::query();

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }
    public function create(array $data)
    {
        return Satuan::create($data);
    }

    public function edit($id)
    {
        return Satuan::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $category = Satuan::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function delete($id)
    {
        $category = Satuan::findOrFail($id);
        return $category->delete();
    }
}