<?php

namespace Database\Factories;

use App\Models\DetailPenerimaanBarang;
use App\Models\Penerimaan;
use App\Models\Stok;
use App\Models\Category;
use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPenerimaanBarangFactory extends Factory
{
    protected $model = DetailPenerimaanBarang::class;

    public function definition(): array
    {
        $satuan   = Satuan::factory()->create();
        $kategori = Category::factory()->create();

        $stok = Stok::factory()->create([
            'category_id' => $kategori->id,
            'satuan_id'   => $satuan->id,
        ]);

        return [
            'penerimaan_id' => Penerimaan::factory(),
            'stok_id'       => $stok->id,
            'quantity'      => $this->faker->numberBetween(1, 20),
            'harga'         => $this->faker->numberBetween(1000, 5000),
            'total_harga'   => function (array $attr) {
                return $attr['quantity'] * $attr['harga'];
            },
            'is_layak'      => true,
            'is_paid'       => false,
        ];
    }
}
