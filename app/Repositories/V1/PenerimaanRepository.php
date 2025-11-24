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
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $filters['per_page'] ?? 10;
        $penerimaan = $query->paginate($perPage);

        return $penerimaan;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $penerimaan = Penerimaan::create([
                'user_id' => 4,
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

            $userId = 4;
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
        $penerimaan = Penerimaan::with([
            'detailBarang.stok.category',
            'detailBarang.stok.satuan',
            'detailPegawai.pegawai.jabatan',
            'category'
        ])->findOrFail($id);

        // Ambil field penting dari penerimaan
        $data = [
            'id' => $penerimaan->id,
            'no_surat' => $penerimaan->no_surat,
            'deskripsi' => $penerimaan->deskripsi,
            'status' => $penerimaan->status,
            'category' => [
                'id' => $penerimaan->category->id,
                'name' => $penerimaan->category->name
            ],
            'detail_barang' => $penerimaan->detailBarang->map(function ($item) {
                return [
                    'id' => $item->id,
                    'stok_id' => $item->stok_id,
                    'nama_stok' => $item->stok->name,
                    'nama_category' => $item->stok->category->name,
                    'nama_satuan' => $item->stok->satuan->name,
                    'harga' => $item->harga,
                    'quantity' => $item->quantity,
                    'total_harga' => $item->total_harga,
                    'is_layak' => $item->is_layak,
                ];
            }),
            'detail_pegawai' => $penerimaan->detailPegawai->map(function ($item) {
                return [
                    'id' => $item->pegawai->id,
                    'name' => $item->pegawai->name,
                    'nip' => $item->pegawai->nip,
                    'jabatan_id' => $item->pegawai->jabatan->id,
                    'jabatan_name' => $item->pegawai->jabatan->name,
                    'alamat_satker' => $item->alamat_staker
                ];
            })
        ];

        return $data;
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $penerimaan = Penerimaan::findOrFail($id);
            $updateFields = [];

            if (isset($data['no_surat'])) {
                $updateFields['no_surat'] = $data['no_surat'];
            }

            if (isset($data['category_id'])) {
                $updateFields['category_id'] = $data['category_id'];
            }

            if (isset($data['deskripsi'])) {
                $updateFields['deskripsi'] = $data['deskripsi'];
            }

            if (isset($data['status'])) {
                $updateFields['status'] = $data['status'];
            }

            if (!empty($updateFields)) {
                $penerimaan->update($updateFields);
            }

            if (isset($data['detail_barangs']) && is_array($data['detail_barangs'])) {
                $existingBarang = $penerimaan->detailBarang->keyBy('id');
                $processedIds = [];

                foreach ($data['detail_barangs'] as $barang) {
                    $stok = Stok::findOrFail($barang['stok_id']);
                    $harga = $barang['harga'] ?? $barang['price'] ?? $stok->price;

                    $barangData = [
                        'stok_id' => $stok->id,
                        'quantity' => $barang['quantity'],
                        'harga' => $harga,
                        'total_harga' => $harga * $barang['quantity'],
                    ];
                    if (isset($barang['is_layak'])) {
                        $barangData['is_layak'] = $barang['is_layak'];
                    }
                    if (!empty($barang['id']) && $existingBarang->has($barang['id'])) {
                        $existingBarang[$barang['id']]->update($barangData);
                        $processedIds[] = $barang['id'];
                    } else {
                        $newDetail = DetailPenerimaanBarang::create([
                            'penerimaan_id' => $penerimaan->id,
                            ...$barangData
                        ]);
                        $processedIds[] = $newDetail->id;
                    }
                }
            }

            if (!empty($data['deleted_barang_ids'])) {
                $penerimaan->detailBarang()
                    ->whereIn('id', $data['deleted_barang_ids'])
                    ->delete();
            }

            if (isset($data['pegawais']) && is_array($data['pegawais'])) {
                $existingPegawai = $penerimaan->detailPegawai->keyBy('pegawai_id');

                foreach ($data['pegawais'] as $pegawai) {
                    $pegawaiId = $pegawai['pegawai_id'];

                    if ($existingPegawai->has($pegawaiId)) {
                        $updateData = [];
                        if (isset($pegawai['alamat_staker'])) {
                            $updateData['alamat_staker'] = $pegawai['alamat_staker'];
                        }

                        if (!empty($updateData)) {
                            $existingPegawai[$pegawaiId]->update($updateData);
                        }
                    } else {
                        DetailPenerimaanPegawai::create([
                            'penerimaan_id' => $penerimaan->id,
                            'pegawai_id' => $pegawaiId,
                            'alamat_staker' => $pegawai['alamat_staker'] ?? null,
                        ]);
                    }
                }
            }

            Monitoring::create([
                'user_id' => 4,
                'time' => now()->format('H:i:s'),
                'date' => now()->format('Y-m-d'),
                'activity' => "Mengupdate penerimaan: {$penerimaan->no_surat}",
            ]);

            return $penerimaan->fresh()->load(['detailBarang', 'detailPegawai.pegawai', 'category']);
        });
    }

    public function delete($id)
    {
        $penerimaan = Penerimaan::findOrFail($id);
        $penerimaan->detailBarang()->delete();
        $penerimaan->detailPegawai()->delete();

        $userId = 4;
        Monitoring::create([
            'user_id' => $userId,
            'time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'activity' => "Menghapus penerimaan: {$penerimaan->no_surat}",
        ]);
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
        $userId = 3;
        Monitoring::create([
            'user_id' => $userId,
            'time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'activity' => "Mengkonfirmasi penerimaan: {$penerimaan->no_surat}",
        ]);
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
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }
}
