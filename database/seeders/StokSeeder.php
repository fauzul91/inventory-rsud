<?php

namespace Database\Seeders;

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
        $satuanIds = [1, 2, 3]; // contoh: pcs, box, pack

        $stokItems = [
            ['name' => 'Pulpen', 'category' => 1, 'price' => 2000, 'stok_2024' => 100, 'minimum_stok' => 10],
            ['name' => 'Buku Catatan', 'category' => 1, 'price' => 5000, 'stok_2024' => 50, 'minimum_stok' => 5],
            ['name' => 'Kertas HVS A4', 'category' => 5, 'price' => 50000, 'stok_2024' => 200, 'minimum_stok' => 20],
            ['name' => 'Tinta Printer', 'category' => 2, 'price' => 150000, 'stok_2024' => 30, 'minimum_stok' => 5],
            ['name' => 'Stabilizer Listrik', 'category' => 3, 'price' => 250000, 'stok_2024' => 10, 'minimum_stok' => 2],
            ['name' => 'Flashdisk 16GB', 'category' => 4, 'price' => 75000, 'stok_2024' => 40, 'minimum_stok' => 5],
            ['name' => 'Stapler', 'category' => 1, 'price' => 15000, 'stok_2024' => 30, 'minimum_stok' => 5],
            ['name' => 'Kabel Listrik 5m', 'category' => 3, 'price' => 30000, 'stok_2024' => 25, 'minimum_stok' => 5],
            ['name' => 'Kertas Cover A4', 'category' => 5, 'price' => 10000, 'stok_2024' => 50, 'minimum_stok' => 5],
            ['name' => 'Sikat Lantai', 'category' => 7, 'price' => 20000, 'stok_2024' => 15, 'minimum_stok' => 3],
            ['name' => 'Cat Tembok', 'category' => 6, 'price' => 150000, 'stok_2024' => 20, 'minimum_stok' => 5],
            ['name' => 'Penghapus', 'category' => 1, 'price' => 1000, 'stok_2024' => 100, 'minimum_stok' => 10],
            ['name' => 'Besi Hollow', 'category' => 6, 'price' => 50000, 'stok_2024' => 30, 'minimum_stok' => 5],
            ['name' => 'Tisu Basah', 'category' => 7, 'price' => 5000, 'stok_2024' => 60, 'minimum_stok' => 10],
            ['name' => 'CD-R', 'category' => 4, 'price' => 10000, 'stok_2024' => 40, 'minimum_stok' => 5],
        ];

        foreach ($stokItems as $item) {
            Stok::firstOrCreate([
                'name' => $item['name'],
                'category_id' => $item['category'],
                'stok_2024' => $item['stok_2024'],
                'minimum_stok' => $item['minimum_stok'],
                'total_stok' => rand($item['stok_2024'], $item['stok_2024'] + 50), // total stok beda
                'price' => $item['price'],
                'satuan_id' => $satuanIds[array_rand($satuanIds)],
            ]);
        }
    }
}
