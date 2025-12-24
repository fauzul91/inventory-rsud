<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PegawaiRepositoryInterface;
use App\Models\Pegawai;

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

    public function getAll(array $filters = [])
    {
        $query = Pegawai::select('id', 'name', 'nip', 'phone', 'status', 'jabatan_id')
            ->with([
                'jabatan' => function ($q) {
                    $q->select('id', 'name');
                }
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('jabatan', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['jabatan_id'])) {
            $query->where('jabatan_id', $filters['jabatan_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('name', 'asc');

        $perPage = $filters['per_page'] ?? 10;
        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(function ($pegawai) {
            return [
                'id' => $pegawai->id,
                'name' => $pegawai->name,
                'nip' => $pegawai->nip,
                'phone' => $pegawai->phone,
                'status' => $pegawai->status,
                'jabatan' => $pegawai->jabatan ? $pegawai->jabatan->name : null,
            ];
        });

        return $paginated;
    }

    public function findById($id)
    {
        return Pegawai::with('jabatan:id,name')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Pegawai::create($data);
    }

    public function update(array $data, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->update($data);
        return $pegawai;
    }

    public function toggleStatus($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->status = $pegawai->status === 'active' ? 'inactive' : 'active';
        $pegawai->save();

        return $pegawai;
    }
}
