<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Obat', 'Alat Kesehatan', 'Reagen Lab', 'Bahan Habis Pakai', 'BMHP'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
