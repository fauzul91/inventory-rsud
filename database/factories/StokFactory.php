<?php

namespace Database\Factories;

use App\Models\Stok;
use Illuminate\Database\Eloquent\Factories\Factory;

class StokFactory extends Factory
{
    protected $model = Stok::class;

    public function definition(): array
    {
        return [
            'name'         => $this->faker->words(3, true),
            'minimum_stok' => $this->faker->numberBetween(1, 50),
        ];
    }
}
