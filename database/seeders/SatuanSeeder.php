<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuans = [
            'pcs',
            'kg',
            'gram',
            'liter',
            'ml',
            'meter',
            'cm',
            'rim',
            'pack',
            'box',
            'botol',
            'roll',
            'set',
            'bungkus',
            'karton',
            'lembar',
            'kgnetto',
            'sachet',
            'tube',
            'pasang',
        ];

        foreach ($satuans as $satuan) {
            Satuan::firstOrCreate(['name' => $satuan]);
        }

        echo "Seeder satuan barang selesai.\n";
    }
}