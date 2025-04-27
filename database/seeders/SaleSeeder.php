<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Company;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $operadores = User::where('role_id', 1)->pluck('id')->toArray();
        $productos = Product::pluck('id')->toArray();
        $empresas = Company::pluck('id')->toArray();

        if (empty($operadores) || empty($productos) || empty($empresas)) {
            return; // Si no hay datos, no hacer nada
        }

        foreach (range(1, 20) as $i) {
            Sale::create([
                'company_id' => fake()->randomElement($empresas), // ✅ Asignamos empresa real aquí

                'company_name' => fake()->company(),
                'cif' => fake()->bothify('??########'),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'province' => fake()->state(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->companyEmail(),
                'activity' => fake()->catchPhrase(),
                'cnae' => fake()->numerify('####'),

                'operator_id' => fake()->randomElement($operadores),
                'sale_date' => now()->subDays(rand(1, 180)),
                'product_id' => fake()->randomElement($productos),
                'business_line_id' => rand(1, 2),
                'tramitator_id' => fake()->randomElement($operadores),
                'processing_date' => now()->subDays(rand(1, 30)),
                'contract_number' => fake()->bothify('CTR-#####'),
                'commission_amount' => fake()->randomFloat(2, 50, 500),
                'commission_paid_date' => null,
                'liquidated_by' => null,
                'liquidation_date' => null,

                'legal_representative' => fake()->name(),
                'legal_representative_dni' => fake()->bothify('DNI-#####'),
                'legal_representative_phone' => fake()->phoneNumber(),

                'gestoria_cif' => fake()->bothify('GESTORIA-#####'),
                'gestoria_phone' => fake()->phoneNumber(),
                'gestoria_email' => fake()->companyEmail(),

                'student_dni' => fake()->bothify('STUDENT-#####'),
                'student_phone' => fake()->phoneNumber(),
                'student_email' => fake()->companyEmail(),

                'company_iban' => fake()->iban('ES'),
                'ss_company' => fake()->bothify('SS-#####'),
                'ss_student' => fake()->bothify('SS-#####'),

                'status' => 'pending',
            ]);
        }
    }
}
