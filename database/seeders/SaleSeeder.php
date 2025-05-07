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
        $productos = Product::all();
        $empresas = Company::pluck('id')->toArray();

        if ($operadores === [] || $productos->isEmpty() || $empresas === []) {
            return; // Si no hay datos, no hacer nada
        }

        foreach (range(1, 20) as $i) {
            $product = $productos->random();
            $productId = $product->id;

            Sale::create([
                'company_id' => fake()->randomElement($empresas),
                'company_name' => fake()->company(),
                'cif' => fake()->bothify('??########'),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'province' => fake()->state(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->companyEmail(),
                'activity' => fake()->catchPhrase(),
                'cnae' => fake()->numerify('####'),
                'contact_person' => fake()->name(),

                'operator_id' => fake()->randomElement($operadores),
                'sale_date' => now()->subDays(rand(1, 180)),
                'product_id' => $productId,
                'business_line_id' => $product->business_line_id,
                'sale_price' => $product->price,
                'commission_amount' => round($product->price * ($product->commission_percentage / 100), 2),
                'tramitator_id' => fake()->randomElement($operadores),
                'processing_date' => now()->subDays(rand(1, 30)),
                'contract_number' => fake()->bothify('CTR-#####'),
                'commission_paid_date' => null,
                'liquidated_by' => null,
                'liquidation_date' => null,

                'legal_representative_name' => fake()->name(),
                'legal_representative_dni' => fake()->bothify('DNI-#####'),
                'legal_representative_phone' => fake()->phoneNumber(),

                'gestoria_name' => fake()->name(),
                'gestoria_cif' => fake()->bothify('GESTORIA-#####'),
                'gestoria_phone' => fake()->phoneNumber(),
                'gestoria_email' => fake()->companyEmail(),

                'student_name' => fake()->name(),
                'student_dni' => fake()->bothify('STUDENT-#####'),
                'student_ss' => fake()->bothify('SS-#####'),
                'student_phone' => fake()->phoneNumber(),
                'student_email' => fake()->companyEmail(),

                'company_iban' => fake()->iban('ES'),
                'ss_company' => fake()->bothify('SS-#####'),
                'ss_student' => fake()->bothify('SS-#####'),

                'status' => 'pendiente',
            ]);
        }
    }
}
