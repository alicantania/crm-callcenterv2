<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'middle_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => $this->faker->phoneNumber(),
            'mobile' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'birth_date' => $this->faker->date(),
            'identification_number' => $this->faker->dni(),
            'password' => bcrypt('password'),
            'extension' => $this->faker->numberBetween(1000, 9999),
            'contract_start_date' => now()->subYear(),
            'contract_end_date' => null,
            'contract_hours' => 40,
            'commission_rate' => $this->faker->randomFloat(2, 5, 20),
            'personal_commissions' => $this->faker->randomFloat(2, 0, 1000),
            'remember_token' => Str::random(20),
            'role_id' => Role::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
