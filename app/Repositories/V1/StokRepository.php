<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\StokRepositoryInterface;
use App\Models\Monitoring;
use App\Models\Stok;

class StokRepository implements StokRepositoryInterface
{
    public function getAllStoksForSelect(?int $categoryId = null)
    {
        $query = Stok::select('id', 'name')->orderBy('name', 'asc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->get();
    }
}