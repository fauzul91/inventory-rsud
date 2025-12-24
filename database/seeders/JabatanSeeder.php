<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            'Direktur',
            'Kepala Bidang Keuangan dan Perencanaan',
            'Bendahara Pengeluaran BLUD',
            'Bendahara Penerimaan BLUD',
            'PeJabat Pelaksana Teknis Kegiatan 1',
            'PeJabat Pelaksana Teknis Kegiatan 2',
            'Pejabat Pembuat Komitmen (PPK) 1',
            'Pejabat Pembuat Komitmen (PPK) 2',
            'Pejabat Pembuat Komitmen (PPK) 3',
            'Pejabat Pembuat Komitmen (PPK) 4',
            'Pejabat Pembuat Komitmen (PPK) 5',
            'Pejabat Pembuat Komitmen (PPK) 6',
            'Pejabat Pembuat Komitmen (PPK) 7',
            'Pejabat Pembuat Komitmen (PPK) 8',
            'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
            'Pejabat Pengadaan 1',
            'Pejabat Pengadaan 2',
            'Tim Teknis',
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::firstOrCreate(['name' => $jabatan]);
        }

        echo "Seeder jabatan RSU Balung selesai.\n";
    }
}
