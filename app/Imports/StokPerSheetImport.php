<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use App\Models\Pegawai;
use App\Models\Penerimaan;
use App\Models\Satuan;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StokPerSheetImport implements ToCollection, WithHeadingRow
{
    public function __construct(private string $kategori)
    {
    }

    public function collection(Collection $rows)
    {
        $categoryName = Str::title($this->kategori);
        $category = Category::firstOrCreate([
            'name' => $categoryName
        ]);

        $penerimaan = Penerimaan::firstOrCreate(
            [
                'no_surat' => 'STOK-2025-' . strtoupper($this->kategori)
            ],
            [
                'category_id' => $category->id,
                'status' => 'paid',
                'deskripsi' => 'Migrasi stok tahun 2025 kategori ' . $this->kategori,
                'user_id' => User::role('admin-gudang-umum')->first()->id ?? 1,
            ]
        );
        $penerimaan->timestamps = false;
        $penerimaan->created_at = Carbon::create(2025, 12, 31, 23, 59, 59);
        $penerimaan->updated_at = Carbon::create(2025, 12, 31, 23, 59, 59);
        $penerimaan->save();

        foreach ($rows as $row) {

            if (!trim($row['kode'] ?? '')) {
                continue;
            }
            $namaStok = Str::title(trim($row['nama']));
            $stok = Stok::firstOrCreate(
                [
                    'name' => $namaStok,
                ],
                [
                    'minimum_stok' => random_int(3, 7),
                    'category_id' => $category->id,
                    'satuan_id' => $this->getSatuan($row['satuan']),
                ]
            );

            $detailBarang = DetailPenerimaanBarang::firstOrNew([
                'penerimaan_id' => $penerimaan->id,
                'stok_id' => $stok->id,
            ]);

            if (!$detailBarang->exists) {
                $detailBarang->quantity = (int) $row['stok'];
                $detailBarang->harga = 0;
                $detailBarang->total_harga = 0;
                $detailBarang->is_layak = true;
                $detailBarang->is_paid = true;

                $detailBarang->timestamps = false;
                $detailBarang->created_at = Carbon::create(2025, 12, 31, 23, 59, 59);
                $detailBarang->updated_at = Carbon::create(2025, 12, 31, 23, 59, 59);

                $detailBarang->save();
            }

            $alamatJember = [
                'Jl. Kalimantan No. 37, Sumbersari, Jember',
                'Jl. Jawa No. 12, Tegal Boto, Jember',
                'Jl. Gajah Mada No. 175, Kaliwates, Jember',
                'Jl. Hayam Wuruk No. 50, Sempusari, Jember',
                'Jl. Mastrip No. 5, Sumbersari, Jember',
                'Jl. Riau No. 10, Sumbersari, Jember',
                'Jl. Teuku Umar No. 22, Tegal Besar, Jember',
                'Jl. Sultan Agung No. 101, Kepatihan, Jember',
                'Jl. Karimata No. 45, Sumbersari, Jember',
                'Jl. Letjen Panjaitan No. 88, Sumbersari, Jember',
            ];
            $pegawaiIds = Pegawai::pluck('id')->toArray();
            $tempPegawaiIds = $pegawaiIds;
            shuffle($tempPegawaiIds);

            for ($p = 0; $p < 2; $p++) {
                if (!isset($tempPegawaiIds[$p]))
                    break;

                $detailPegawai = new DetailPenerimaanPegawai();
                $detailPegawai->penerimaan_id = $penerimaan->id;
                $detailPegawai->pegawai_id = $tempPegawaiIds[$p];
                $detailPegawai->alamat_staker = $alamatJember[array_rand($alamatJember)];
                $detailPegawai->urutan = $p + 1;

                $detailPegawai->timestamps = false;
                $detailPegawai->created_at = Carbon::create(2025, 12, 31, 23, 59, 59);
                $detailPegawai->updated_at = Carbon::create(2025, 12, 31, 23, 59, 59);

                $detailPegawai->save();
            }
        }
    }

    private function getSatuan($nama)
    {
        $namaSatuan = strtolower(trim($nama));
        return Satuan::firstOrCreate([
            'name' => $namaSatuan
        ])->id;
    }
}
