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

        if (!empty($filters['search'])) {
            $query->where('no_surat', 'like', '%' . $filters['search'] . '%');
        }

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

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $penerimaan = Penerimaan::create([
                'no_surat' => $data['no_surat'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'status' => 'draft',
            ]);

            // Simpan detail barang
            if (!empty($data['detail_barangs'])) {
                foreach ($data['detail_barangs'] as $barang) {
                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'nama_barang' => $barang['nama_barang'],
                        'category_id' => $barang['category_id'],
                        'satuan_id' => $barang['satuan_id'],
                        'quantity' => $barang['quantity'],
                        'harga' => $barang['harga'],
                        'total_harga' => $barang['quantity'] * $barang['harga'],
                        'is_layak' => false,
                    ]);
                }
            }

            // Simpan detail pegawai
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
        return Penerimaan::with(['detailBarang', 'detailPegawai.pegawai'])->findOrFail($id);
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $penerimaan = Penerimaan::findOrFail($id);
            $penerimaan->update([
                'no_surat' => $data['no_surat'] ?? $penerimaan->no_surat,
                'deskripsi' => $data['deskripsi'] ?? $penerimaan->deskripsi,
                'status' => $data['status'] ?? $penerimaan->status,
            ]);

            // Update detail barang
            if (!empty($data['detail_barangs'])) {
                $penerimaan->detailBarang()->delete();
                foreach ($data['detail_barangs'] as $barang) {
                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'nama_barang' => $barang['nama_barang'],
                        'category_id' => $barang['category_id'],
                        'satuan_id' => $barang['satuan_id'],
                        'quantity' => $barang['quantity'],
                        'harga' => $barang['harga'],
                        'total_harga' => $barang['quantity'] * $barang['harga'],
                        'is_layak' => $barang['is_layak'] ?? false,
                    ]);
                }
            }

            // Update detail pegawai
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

            return $penerimaan->load(['detailBarang', 'detailPegawai.pegawai']);
        });
    }

    public function delete($id)
    {
        $penerimaan = Penerimaan::findOrFail($id);
        $penerimaan->detailBarang()->delete();
        $penerimaan->detailPegawai()->delete();
        return $penerimaan->delete();
    }

    public function setLayak($detailId, $isLayak)
    {
        $detail = DetailPenerimaanBarang::findOrFail($detailId);
        $detail->update(['is_layak' => (bool) $isLayak]);
        return $detail;
    }

    public function updateConfirmationStatus($id)
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
