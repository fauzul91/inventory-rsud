<?php

namespace Database\Factories;

use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Models\Stok;
use App\Models\Category;
use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPemesananFactory extends Factory
{
    protected $model = DetailPemesanan::class;

    public function definition(): array
    {
        $satuan   = Satuan::factory()->create();
        $kategori = Category::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        return [
            'pemesanan_id' => Pemesanan::factory(),
            'stok_id'      => $stok->id,
            'quantity'     => $this->faker->numberBetween(1, 10),
        ];
    }
}
