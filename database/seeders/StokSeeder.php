<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Satuan;
use App\Models\Stok;
use Illuminate\Database\Seeder;

class StokSeeder extends Seeder
{
    public function run(): void
    {
        $categoryMap = Category::pluck('id', 'name');

        $stokData = [
            'ATK' => [
                'Pulpen' => 'Pcs',
                'Pensil' => 'Pcs',
                'Penghapus' => 'Pcs',
                'Spidol' => 'Pcs',
                'Penggaris' => 'Pcs'
            ],
            'Cetak' => [
                'Kertas HVS A4' => 'Rim',
                'Kertas Foto' => 'Pack',
                'Label Printer' => 'Roll',
                'Amplop' => 'Box',
                'Buku Nota' => 'Buku'
            ],
            'Alat Listrik' => [
                'Stop Kontak' => 'Unit',
                'Lampu LED' => 'Pcs',
                'Extension Cord' => 'Unit',
                'Saklar' => 'Pcs',
                'Baterai' => 'Pack'
            ],
            'Bahan Komputer' => [
                'Flashdisk' => 'Unit',
                'HDD Eksternal' => 'Unit',
                'Mouse' => 'Unit',
                'Keyboard' => 'Unit',
                'Monitor' => 'Unit'
            ],
            'Bahan Bangunan' => [
                'Semen' => 'Sak',
                'Pasir' => 'M3',
                'Cat Tembok' => 'Pail',
                'Paku' => 'Kg',
                'Kayu Balok' => 'Batang'
            ],
            'Bahan Pembersih' => [
                'Sabun Cuci' => 'Botol',
                'Detergen' => 'Bungkus',
                'Disinfektan' => 'Jerigen',
                'Sapu' => 'Pcs',
                'Lap Serbaguna' => 'Lusin'
            ],
        ];

        foreach ($stokData as $categoryName => $items) {
            $categoryId = $categoryMap[$categoryName] ?? null;

            if ($categoryId) {
                foreach ($items as $itemName => $satuanName) {
                    $satuan = Satuan::firstOrCreate(['name' => $satuanName]);

                    Stok::create([
                        'name' => $itemName,
                        'category_id' => $categoryId,
                        'minimum_stok' => rand(5, 10),
                        'satuan_id' => $satuan->id,
                    ]);
                }
            }
        }
    }
}