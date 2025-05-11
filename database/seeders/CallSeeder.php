<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Call;
use App\Models\User;
use App\Models\Company;
use Faker\Factory as Faker;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $operadores = User::where('role_id', 1)->pluck('id')->toArray();
        $empresas = Company::pluck('id')->toArray();

        if (empty($operadores) || empty($empresas)) {
            return;
        }

        foreach (range(1, 800) as $i) {
            Call::create([
                'company_id' => $faker->randomElement($empresas),
                'user_id' => $faker->randomElement($operadores),
                'call_date' => $faker->dateTimeBetween('-3 months', 'now'),
                'duration' => rand(60, 900),
                'status' => $faker->randomElement([
                    'interesado', 'no contesta', 'volver a llamar', 'venta', 'error', 'no interesa',
                ]),
                'notes' => $faker->sentence(),
            ]);
        }
    }
}
