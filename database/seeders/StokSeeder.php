<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Satuan;
use App\Models\Stok;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuans = Satuan::all();
        $categoryMap = Category::pluck('id', 'name');

        $stokData = [
            'ATK' => ['Pulpen', 'Pensil', 'Penghapus', 'Spidol', 'Penggaris'],
            'Cetak' => ['Kertas HVS A4', 'Kertas Foto', 'Label Printer', 'Amplop', 'Buku Nota'],
            'Alat Listrik' => ['Stop Kontak', 'Lampu LED', 'Extension Cord', 'Saklar', 'Baterai'],
            'Bahan Komputer' => ['Flashdisk', 'HDD Eksternal', 'Mouse', 'Keyboard', 'Monitor'],
            'Kertas dan Cover' => ['Cover Plastik', 'Karton', 'Sticker A4', 'Kertas Label', 'Binder'],
            'Bahan Bangunan' => ['Semen', 'Pasir', 'Cat Tembok', 'Paku', 'Kayu Balok'],
            'Bahan Pembersih' => ['Sabun Cuci', 'Detergen', 'Disinfektan', 'Sapu', 'Lap Serbaguna'],
        ];

        foreach ($stokData as $categoryName => $items) {
            $categoryId = $categoryMap[$categoryName];

            foreach ($items as $name) {
                Stok::create([
                    'name' => $name,
                    'category_id' => $categoryId,
                    'minimum_stok' => rand(5, 8),
                    'satuan_id' => $satuans->random()->id,
                ]);
            }
        }
    }
}
