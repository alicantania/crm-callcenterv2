<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallFactory extends Factory
{
    protected $model = Call::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'company_id' => Company::inRandomOrder()->first()?->id ?? 1,
            'call_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'duration' => $this->faker->numberBetween(30, 600), // segundos
            'status' => $this->faker->randomElement(['completada', 'no contestó', 'contacto','ocupado', 'error', 'volver a llamar']),
            'notes' => $this->faker->optional()->sentence(8),
        ];
    }
}
