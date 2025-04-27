<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Crear 20 empresas
        Company::factory(20)->create()->each(function ($empresa, $index) {
            if ($index < 10) {
                // 50% de empresas SIN operador (para llamar)
                $empresa->updateQuietly([
                    'assigned_operator_id' => null,
                ]);
            } else {
                // 50% de empresas ASIGNADAS a un operador aleatorio
                $operador = User::where('role_id', 1)->inRandomOrder()->first();
                $empresa->updateQuietly([
                    'assigned_operator_id' => $operador?->id,
                ]);
            }
        });
    }
}
