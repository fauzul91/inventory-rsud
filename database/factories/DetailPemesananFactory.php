<?php

namespace Database\Factories;

use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Models\Stok;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPemesananFactory extends Factory
{
    protected $model = DetailPemesanan::class;

    public function definition()
    {
        return [
            'pemesanan_id' => Pemesanan::factory(),
            'stok_id' => Stok::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
