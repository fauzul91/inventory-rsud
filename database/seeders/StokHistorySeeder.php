<?php

namespace Database\Seeders;

use App\Models\Stok;
use App\Models\StokHistory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StokHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stoks = Stok::all();

        foreach ($stoks as $stok) {
            for ($batch = 1; $batch <= 2; $batch++) {
                $quantity = rand(20, 100);

                StokHistory::create([
                    'stok_id' => $stok->id,
                    'year' => 2024 + $batch,
                    'quantity' => $quantity,
                    'used_qty' => 0,
                    'remaining_qty' => $quantity,
                    'source' => 'seeder',
                    'source_id' => null,
                ]);
            }
        }
    }
}
