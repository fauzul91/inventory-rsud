<?php

namespace Database\Factories;

use App\Models\Penerimaan;
use App\Models\Stok;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPenerimaanBarangFactory extends Factory
{
    public function definition()
    {
        $price = $this->faker->numberBetween(10000, 50000);
        $qty = $this->faker->numberBetween(1, 5);

        return [
            'penerimaan_id' => Penerimaan::factory(),
            'stok_id' => Stok::factory(),
            'quantity' => $qty,
            'quantity_layak' => $qty,
            'quantity_tidak_layak' => 0,
            'harga' => $price,
            'total_harga' => $price * $qty,
            'is_paid' => false,
        ];
    }
}
