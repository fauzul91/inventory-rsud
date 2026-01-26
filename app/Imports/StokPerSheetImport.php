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

            DetailPenerimaanBarang::firstOrCreate(
                [
                    'penerimaan_id' => $penerimaan->id,
                    'stok_id' => $stok->id,
                ],
                [
                    'quantity' => (int) $row['stok'],
                    'harga' => 0,
                    'total_harga' => 0,
                    'is_layak' => true,
                    'is_paid' => true
                ]
            );

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

                DetailPenerimaanPegawai::create([
                    'penerimaan_id' => $penerimaan->id,
                    'pegawai_id' => $tempPegawaiIds[$p],
                    'alamat_staker' => $alamatJember[array_rand($alamatJember)],
                    'urutan' => $p + 1,
                ]);
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
