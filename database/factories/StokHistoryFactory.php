<?php

namespace Database\Factories;

use App\Models\Stok;
use Illuminate\Database\Eloquent\Factories\Factory;

class StokHistoryFactory extends Factory
{
    public function definition()
    {
        $qty = $this->faker->numberBetween(1, 100);

        return [
            'stok_id' => Stok::factory(),
            'year' => now()->year,
            'quantity' => $qty,
            'used_qty' => 0,
            'remaining_qty' => $qty,
            'source' => null,
            'source_id' => null,
        ];
    }
}
