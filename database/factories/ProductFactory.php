<?php

namespace Database\Factories;

use App\Models\BusinessLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 50, 500), // precios entre 50€ y 500€
            'commission_type' => $this->faker->randomElement(['fijo', 'porcentaje']),
            'commission_value' => function (array $attributes) {
                return $attributes['commission_type'] === 'fijo'
                    ? fake()->randomFloat(2, 10, 100)  // comisión fija entre 10 y 100€
                    : fake()->randomFloat(2, 5, 20);  // porcentaje entre 5% y 20%
            },
            'available' => true,
            'business_line_id' => BusinessLine::inRandomOrder()->first()?->id ?? BusinessLine::factory(),
        ];
    }
}
