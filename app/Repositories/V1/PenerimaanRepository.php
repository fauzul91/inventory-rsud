<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Models\Monitoring;
use App\Models\Penerimaan;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;

class PenerimaanRepository implements PenerimaanRepositoryInterface
{
    public function getAllPenerimaan(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])->where('status', 'pending');

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

        return $penerimaan;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $penerimaan = Penerimaan::create([
                'user_id' => auth()->id() ?? 1,
                'no_surat' => $data['no_surat'],
                'category_id' => $data['category_id'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'status' => 'pending',
            ]);

            if (!empty($data['detail_barangs'])) {
                foreach ($data['detail_barangs'] as $barang) {
                    $stok = Stok::findOrFail($barang['stok_id']);
                    $harga = isset($barang['price']) && $barang['price'] !== ''
                        ? $barang['price']
                        : $stok->price;

                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'stok_id' => $stok->id,
                        'quantity' => $barang['quantity'],
                        'harga' => $harga,                         
                        'total_harga' => $harga * $barang['quantity'], 
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

            $userId = auth()->id ?? 1;
            Monitoring::create([
                'user_id' => $userId,
                'time' => now()->format('H:i:s'),
                'date' => now()->format('Y-m-d'),
                'activity' => "Membuat penerimaan: {$penerimaan->no_surat}",
            ]);
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
                $stok = Stok::findOrFail($barang['stok_id']);

                if (!empty($barang['id']) && $existingBarang->has($barang['id'])) {
                    $existingBarang[$barang['id']]->update([
                        'stok_id' => $stok->id,
                        'quantity' => $barang['quantity'],
                        'harga' => $stok->price,
                        'total_harga' => $stok->price * $barang['quantity'],
                        'is_layak' => $barang['is_layak'] ?? null,
                    ]);
                    $existingBarang->forget($barang['id']);
                } else {
                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'stok_id' => $stok->id,
                        'quantity' => $barang['quantity'],
                        'harga' => $stok->price,
                        'total_harga' => $stok->price * $barang['quantity'],
                        'is_layak' => $barang['is_layak'] ?? null,
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

    public function markBarangLayak($penerimaanId, $detailId, $isLayak)
    {
        $detail = DetailPenerimaanBarang::where('id', $detailId)
            ->where('penerimaan_id', $penerimaanId)
            ->first();

        if (!$detail) {
            return [
                'success' => false,
                'message' => 'Detail barang tidak ditemukan untuk penerimaan ini.'
            ];
        }

        $detail->update(['is_layak' => (bool) $isLayak]);

        return [
            'success' => true,
            'data' => $detail
        ];
    }

    public function confirmPenerimaan($id)
    {
        $penerimaan = Penerimaan::with('detailBarang')->findOrFail($id);

        $masihAdaBelumDinilai = $penerimaan->detailBarang()
            ->whereNull('is_layak')
            ->exists();

        if ($masihAdaBelumDinilai) {
            return [
                'success' => false,
                'message' => 'Masih ada barang yang belum dinilai kelayakannya'
            ];
        }

        $penerimaan->update(['status' => 'confirmed']);

        return [
            'success' => true,
            'data' => $penerimaan->fresh()
        ];
    }
    public function getHistoryPenerimaan(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang', 'bast'])->where('status', 'confirmed');

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
        return $query->paginate($perPage);
    }
}