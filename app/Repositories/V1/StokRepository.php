<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\StokRepositoryInterface;
use App\Models\Monitoring;
use App\Models\Stok;

class StokRepository implements StokRepositoryInterface
{
    public function getAllStoksForSelect()
    {
        return Stok::with('satuan:id,name') 
            ->select('id', 'name', 'satuan_id')
            ->orderBy('name', 'asc')
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
}