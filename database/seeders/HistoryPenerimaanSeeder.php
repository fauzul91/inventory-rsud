<?php

namespace Database\Seeders;

use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use App\Models\Penerimaan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HistoryPenerimaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // for ($i = 1; $i <= 20; $i++) {
        //     $penerimaan = Penerimaan::create([
        //         'user_id' => rand(1, 6), // bisa disesuaikan
        //         'no_surat' => 'NO-' . Str::random(6) . '/' . $i,
        //         'category_id' => rand(1, 6),
        //         'deskripsi' => 'Deskripsi penerimaan ke-' . $i,
        //         'status' => 'confirmed',
        //     ]);

        //     $barangCount = rand(1, 5);
        //     $stokIds = range(1, 10);
        //     shuffle($stokIds);
        //     for ($b = 0; $b < $barangCount; $b++) {
        //         DetailPenerimaanBarang::create([
        //             'penerimaan_id' => $penerimaan->id,
        //             'stok_id' => $stokIds[$b],
        //             'quantity' => rand(1, 20),
        //             'harga' => rand(10000, 50000),
        //             'total_harga' => rand(10000, 50000) * rand(1, 20),
        //             'is_layak' => 1,
        //         ]);
        //     }

        //     $pegawaiCount = rand(1, 3);
        //     $pegawaiIds = range(1, 10);
        //     shuffle($pegawaiIds);
        //     for ($p = 0; $p < $pegawaiCount; $p++) {
        //         DetailPenerimaanPegawai::create([
        //             'penerimaan_id' => $penerimaan->id,
        //             'pegawai_id' => $pegawaiIds[$p],
        //             'alamat_staker' => 'Alamat pegawai ' . $pegawaiIds[$p],
        //         ]);
        //     }
        // }
    }
}
