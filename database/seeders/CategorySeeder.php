<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'ATK',
            'Cetak',
            'Alat Listrik',
            'Bahan Komputer',
            'Kertas dan Cover',
            'Bahan Bangunan',
            'Bahan Pembersih',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category]
            );
        }
    }
}
