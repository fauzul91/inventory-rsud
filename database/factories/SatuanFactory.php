<?php

namespace Database\Factories;

use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SatuanFactory extends Factory
{
    protected $model = Satuan::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'pcs', 'box', 'pak', 'unit', 'botol', 'liter'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
