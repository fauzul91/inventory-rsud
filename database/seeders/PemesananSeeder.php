<?php

namespace Database\Seeders;

use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Models\Stok;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PemesananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = 6;
        $stoks = Stok::pluck('id')->toArray();

        if (empty($user) || empty($stoks)) {
            dd("Seeder gagal: users atau stoks kosong!");
        }
        for ($i = 1; $i <= 10; $i++) {

            $pemesanan = Pemesanan::create([
                'user_id' => $user,
                'nama_pj_instalasi' => "PJ Instalasi " . $i,
                'ruangan' => "Ruangan " . $i,
                'status' => 'pending',
                'tanggal_pemesanan' => now()->subDays(rand(1, 30)),
            ]);
            $detailCount = rand(2, 6);

            for ($j = 1; $j <= $detailCount; $j++) {
                DetailPemesanan::create([
                    'pemesanan_id' => $pemesanan->id,
                    'stok_id' => $stoks[array_rand($stoks)],
                    'quantity' => rand(1, 10),
                ]);
            }
        }
    }
}
