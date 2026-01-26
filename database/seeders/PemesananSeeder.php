<?php

namespace Database\Seeders;

use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Database\Seeder;

class PemesananSeeder extends Seeder
{
    public function run(): void
    {
        $userInstalasi = User::role('instalasi')->first();
        $stoks = Stok::pluck('id')->toArray();

        if (!$userInstalasi) {
            $this->command->error("Seeder Gagal: User dengan role 'instalasi' tidak ditemukan!");
            return;
        }

        if (empty($stoks)) {
            $this->command->error("Seeder Gagal: Tabel stok masih kosong!");
            return;
        }

        $userId = $userInstalasi->id;

        $staffRS = [
            'Suryadi, S.Kep.',
            'dr. Wahyu Hidayat',
            'Siti Aminah, Amd.Keb',
            'Budi Santoso, S.Farm',
            'Lilik Sulistiyowati',
            'Hendra Wijaya, S.T.',
            'Ani Maryani, S.Tr.Keb',
            'drg. Bambang Heru',
            'Eko Prasetyo, Amd.Kep',
            'Dewi Fortuna, S.KM'
        ];

        $ruanganRS = [
            'Instalasi Farmasi',
            'Poli Anak',
            'IGD (Instalasi Gawat Darurat)',
            'Tata Usaha (TU)',
            'Ruang Logistik',
            'Poli Gigi',
            'Instalasi Bedah Sentral',
            'Laboratorium Klinik',
            'Ruang Radiologi',
            'Poli Dalam',
            'Instalasi Gizi',
            'Ruang Rawat Inap Melati'
        ];

        for ($i = 0; $i < 10; $i++) {
            $pemesanan = Pemesanan::create([
                'user_id' => $userId,
                'nama_pj_instalasi' => $staffRS[$i] ?? $staffRS[array_rand($staffRS)],
                'ruangan' => $ruanganRS[array_rand($ruanganRS)],
                'status' => 'pending',
                'tanggal_pemesanan' => now()->subDays(rand(1, 15)),
            ]);

            $detailCount = rand(2, 5);
            $tempStoks = $stoks;
            shuffle($tempStoks);

            for ($j = 0; $j < $detailCount; $j++) {
                if (isset($tempStoks[$j])) {
                    DetailPemesanan::create([
                        'pemesanan_id' => $pemesanan->id,
                        'stok_id' => $tempStoks[$j],
                        'quantity' => rand(1, 5),
                    ]);
                }
            }
        }
    }
}