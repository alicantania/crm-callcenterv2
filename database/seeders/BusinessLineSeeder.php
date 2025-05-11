<?php

namespace Database\Seeders;

use App\Models\BusinessLine;
use Illuminate\Database\Seeder;

class BusinessLineSeeder extends Seeder
{
    public function run(): void
    {
        $lines = [
            ['name' => 'Formación bonificada', 'description' => 'Cursos con créditos FUNDAE'],
            ['name' => 'Implantaciones privadas', 'description' => 'Soluciones sin créditos FUNDAE'],
        ];

        foreach ($lines as $line) {
            BusinessLine::firstOrCreate(['name' => $line['name']], ['description' => $line['description']]);
        }
    }
}

