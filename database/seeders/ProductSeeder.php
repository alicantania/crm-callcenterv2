<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 🧑‍🏫 Cursos de formación bonificada (business_line_id = 1)
        foreach ([
            ['Curso de Prevención de Riesgos Laborales', 300],
            ['Curso de Manipulación de Alimentos', 150],
            ['Curso de Primeros Auxilios', 200],
            ['Curso de Excel Avanzado', 180],
            ['Curso de Inglés Empresarial', 250],
        ] as [$nombre, $precio]) {
            Product::create([
                'name' => $nombre,
                'description' => 'Curso de formación bonificada.',
                'price' => $precio,
                'commission_percentage' => rand(10, 20), // comisión random 10%-20%
                'available' => true,
                'business_line_id' => 1, // formación bonificada
            ]);
        }

        // 📜 Normativas privadas (business_line_id = 2)
        foreach ([
            ['Normativa ISO 9001', 800],
            ['Normativa ISO 14001', 900],
            ['Normativa de Seguridad Alimentaria', 1200],
            ['Implantación LOPD', 1000],
            ['Implantación ISO 45001', 1100],
        ] as [$nombre, $precio]) {
            Product::create([
                'name' => $nombre,
                'description' => 'Implantación de normativa privada.',
                'price' => $precio,
                'commission_percentage' => rand(10, 20),
                'available' => true,
                'business_line_id' => 2, // normativas privadas
            ]);
        }
    }
}
