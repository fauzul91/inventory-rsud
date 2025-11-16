<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PegawaiRepositoryInterface;
use App\Models\Pegawai;
use App\Models\Stok;

class PegawaiRepository implements PegawaiRepositoryInterface
{
    public function getAllForSelect()
    {
        return Pegawai::with('jabatan:id,name') 
            ->select('id', 'name', 'jabatan_id', 'nip')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'jabatan_id' => $item->jabatan_id,
                    'jabatan_name' => $item->jabatan->name ?? null,
                    'nip' => $item->nip,
                ];
            });
    }
}