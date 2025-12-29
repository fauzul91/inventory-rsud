<?php

namespace Database\Seeders;

use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPegawai;
use App\Models\Penerimaan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PenerimaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $penerimaan = Penerimaan::create([
                'user_id' => 4,
                'no_surat' => 'NO-' . Str::random(6) . '/' . $i,
                'category_id' => rand(1, 6),
                'deskripsi' => 'Deskripsi penerimaan ke-' . $i,
                'status' => 'pending',
            ]);

            $barangCount = rand(1, 5);
            $stokIds = range(1, 10);
            shuffle($stokIds);

            for ($b = 0; $b < $barangCount; $b++) {
                $qty = rand(1, 20);
                $harga = rand(10000, 50000);

                DetailPenerimaanBarang::create([
                    'penerimaan_id' => $penerimaan->id,
                    'stok_id' => $stokIds[$b],
                    'quantity' => $qty,
                    'harga' => $harga,
                    'total_harga' => $qty * $harga,
                ]);
            }

            $pegawaiIds = range(1, 10);
            shuffle($pegawaiIds);

            $pegawai1 = $pegawaiIds[0];
            $pegawai2 = $pegawaiIds[1];

            DetailPenerimaanPegawai::create([
                'penerimaan_id' => $penerimaan->id,
                'pegawai_id' => $pegawai1,
                'alamat_staker' => 'Alamat pegawai ' . $pegawai1,
                'urutan' => 1,
            ]);

            DetailPenerimaanPegawai::create([
                'penerimaan_id' => $penerimaan->id,
                'pegawai_id' => $pegawai2,
                'alamat_staker' => 'Alamat pegawai ' . $pegawai2,
                'urutan' => 2,
            ]);
        }
    }
}
