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
            'Direktur RSU Balung',
            'Wakil Direktur',
            'Kepala Bagian Administrasi',
            'Dokter Spesialis',
            'Dokter Umum',
            'Perawat',
            'Bidan',
            'Apoteker',
            'Radiografer',
            'Tenaga Laboratorium',
            'Tenaga Administrasi',
            'Cleaning Service',
            'Satpam',
            'Petugas Gizi',
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::firstOrCreate(['name' => $jabatan]);
        }

        echo "Seeder jabatan RSU Balung selesai.\n";
    }
}
