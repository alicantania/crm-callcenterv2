<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\BusinessLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'available' => $this->faker->boolean(90),
            'business_line_id' => BusinessLine::inRandomOrder()->first()?->id,
        ];
    }
}
