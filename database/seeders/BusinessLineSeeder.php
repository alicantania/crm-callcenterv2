<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessLine;

class BusinessLineSeeder extends Seeder
{
    public function run(): void
    {
        BusinessLine::factory()->create([
            'name' => 'Formación bonificada',
            'description' => 'Cursos con créditos FUNDAE',
        ]);

        BusinessLine::factory()->create([
            'name' => 'Implantaciones privadas',
            'description' => 'Servicios personalizados sin bonificación',
        ]);
    }
}
