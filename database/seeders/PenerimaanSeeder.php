<?php

namespace Database\Seeders;

use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use App\Models\Penerimaan;
use App\Models\Stok;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PenerimaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        for ($i = 1; $i <= 26; $i++) {
            $currentCategoryId = rand(1, 6);

            $penerimaan = Penerimaan::create([
                'user_id' => 4,
                'no_surat' => 'BAST-' . strtoupper(Str::random(4)) . '/' . date('m') . '/' . date('Y') . '/' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'category_id' => $currentCategoryId, // Gunakan variabel kategori
                'deskripsi' => 'Penerimaan barang operasional tahap ke-' . $i,
                'status' => 'pending',
            ]);

            $stokSesuaiKategori = Stok::where('category_id', $currentCategoryId)
                ->pluck('id')
                ->toArray();

            if (count($stokSesuaiKategori) >= 3) {
                $barangCount = rand(3, min(5, count($stokSesuaiKategori)));
                shuffle($stokSesuaiKategori);

                for ($b = 0; $b < $barangCount; $b++) {
                    $qty = rand(5, 50);
                    $harga = rand(5000, 25000);

                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'stok_id' => $stokSesuaiKategori[$b],
                        'quantity' => $qty,
                        'harga' => $harga,
                        'total_harga' => $qty * $harga,
                    ]);
                }
            } else {
                foreach ($stokSesuaiKategori as $stokId) {
                    DetailPenerimaanBarang::create([
                        'penerimaan_id' => $penerimaan->id,
                        'stok_id' => $stokId,
                        'quantity' => $qty,
                        'harga' => $harga,
                        'total_harga' => $qty * $harga,
                    ]);
                }
            }

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
}