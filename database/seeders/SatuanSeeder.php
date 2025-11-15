<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuans = [
            'pcs',        // potong
            'kg',         // kilogram
            'gram',       // gram
            'liter',      // liter
            'ml',         // mililiter
            'meter',      // meter
            'cm',         // sentimeter
            'rim',        // rim kertas
            'pack',       // paket
            'box',        // kotak
            'botol',      // botol
            'roll',       // gulung
            'set',        // set
            'bungkus',    // pack/kantong
            'karton',     // karton
            'lembar',     // lembaran
            'kgnetto',    // kilogram netto
            'sachet',     // sachet
            'tube',       // tabung/tube
            'pasang',     // pasang
        ];

        foreach ($satuans as $satuan) {
            Satuan::firstOrCreate(['name' => $satuan]);
        }

        echo "Seeder satuan barang selesai.\n";
    }
}