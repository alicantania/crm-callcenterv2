<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'cif' => $this->faker->unique()->bothify('??########'),
            'name' => $this->faker->company,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'phone' => $this->faker->randomElement(['9', '6']) . $this->faker->numerify('########'), // 9 dígitos empezando por 9 o 6
            'email' => $this->faker->unique()->safeEmail(), // Aseguramos que sea un email válido
            'activity' => $this->faker->catchPhrase,
            'cnae' => $this->faker->numerify('#####'),
            'assigned_operator_id' => 1, // más adelante lo haremos aleatorio entre IDs reales
        ];
    }
}
