<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use Illuminate\Support\Facades\DB;

class PenerimaanRepository implements PenerimaanRepositoryInterface
{
    public function getAllPenerimaan(array $filters)
    {
        $query = Penerimaan::query();

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('no_surat', 'asc');
        }

        $perPage = $filters['per_page'] ?? 10;
        $penerimaan = $query->paginate($perPage);

        $penerimaan->getCollection()->transform(function($item) {
            return [
                'no_surat' => $item->no_surat,
                'category' => $item->category->name,
                'pegawai' => $item->detailPegawai->map(function($dp) {
                    return [
                        'nama_pegawai' => $dp->pegawai->name ?? null,
                        'role' => $dp->pegawai ? implode(',', $dp->pegawai->getRoleNames()->toArray()) : null,
                    ];
                }),
            ];
        });

        return $penerimaan;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $penerimaan = Penerimaan::create([
                'user_id' => auth()->id(),
                'no_surat' => $data['no_surat'],
                'category_id' => $data['category_id'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'status' => 'draft',
            ]);

            if (!empty($data['detail_barangs'])) {
                foreach ($data['detail_barangs'] as $barang) {
                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'nama_barang' => $barang['nama_barang'],
                        'satuan_id' => $barang['satuan_id'],
                        'quantity' => $barang['quantity'],
                        'harga' => $barang['harga'],
                        'total_harga' => $barang['quantity'] * $barang['harga'],
                        'is_layak' => null,
                    ]);
                }
            }

            if (!empty($data['pegawais'])) {
                foreach ($data['pegawais'] as $pegawai) {
                    DetailPenerimaanPegawai::create([
                        'penerimaan_id' => $penerimaan->id,
                        'pegawai_id' => $pegawai['pegawai_id'],
                        'alamat_staker' => $pegawai['alamat_staker'] ?? null,
                    ]);
                }
            }

            return $penerimaan->load(['detailBarang', 'detailPegawai.pegawai']);
        });
    }

    public function edit($id)
    {
        return Penerimaan::with(['detailBarang', 'detailPegawai.pegawai', 'category'])->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $penerimaan = Penerimaan::findOrFail($id);
            $penerimaan->update([
                'no_surat' => $data['no_surat'] ?? $penerimaan->no_surat,
                'category_id' => $data['category_id'] ?? $penerimaan->category_id,
                'deskripsi' => $data['deskripsi'] ?? $penerimaan->deskripsi,
                'status' => $data['status'] ?? $penerimaan->status,
            ]);

            $existingBarang = $penerimaan->detailBarang->keyBy('id');
            $incomingBarang = collect($data['detail_barangs'] ?? []);

            foreach ($incomingBarang as $barang) {

                if (!empty($barang['id']) && $existingBarang->has($barang['id'])) {
                    $existingBarang[$barang['id']]->update([
                        'nama_barang' => $barang['nama_barang'],
                        'satuan_id'   => $barang['satuan_id'],
                        'quantity'    => $barang['quantity'],
                        'harga'       => $barang['harga'],
                        'total_harga' => $barang['quantity'] * $barang['harga'],
                        'is_layak'    => $barang['is_layak'] ?? null,
                    ]);

                    $existingBarang->forget($barang['id']);
                } 
                else {
                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'nama_barang'   => $barang['nama_barang'],
                        'satuan_id'     => $barang['satuan_id'],
                        'quantity'      => $barang['quantity'],
                        'harga'         => $barang['harga'],
                        'total_harga'   => $barang['quantity'] * $barang['harga'],
                        'is_layak'      => $barang['is_layak'] ?? null,
                    ]);
                }
            }

            foreach ($existingBarang as $old) {
                $old->delete();
            }


            if (!empty($data['pegawais'])) {
                $penerimaan->detailPegawai()->delete();
                foreach ($data['pegawais'] as $pegawai) {
                    DetailPenerimaanPegawai::create([
                        'penerimaan_id' => $penerimaan->id,
                        'pegawai_id' => $pegawai['pegawai_id'],
                        'alamat_staker' => $pegawai['alamat_staker'] ?? null,
                    ]);
                }
            }

            return $penerimaan->load(['detailBarang', 'detailPegawai.pegawai', 'category']);
        });
    }

    public function delete($id)
    {   
        $penerimaan = Penerimaan::findOrFail($id);
        $penerimaan->detailBarang()->delete();
        $penerimaan->detailPegawai()->delete();
        return $penerimaan->delete();
    }

    public function markBarangLayak($detailId, $isLayak)
    {
        $detail = DetailPenerimaanBarang::findOrFail($detailId);
        $detail->update(['is_layak' => (bool) $isLayak]);
        return $detail;
    }

    public function confirmPenerimaan($id)
    {
        $penerimaan = Penerimaan::with('detailBarang')->findOrFail($id);
        $semuaSudahDinilai = !$penerimaan->detailBarang()->whereNull('is_layak')->exists();

        if (!$semuaSudahDinilai) {
            return false;
        }

        $penerimaan->update(['status' => 'confirmed']);
        return $penerimaan;
    }
}
