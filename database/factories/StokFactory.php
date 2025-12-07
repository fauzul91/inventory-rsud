<?php

namespace Database\Factories;

use App\Models\Stok;
use App\Models\Category;
use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;

class StokFactory extends Factory
{
    protected $model = Stok::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'category_id' => Category::factory(),
            'minimum_stok' => $this->faker->randomFloat(2, 1, 50), 
            'price' => $this->faker->numberBetween(1000, 500000),
            'satuan_id' => Satuan::factory(),
        ];
    }
}
