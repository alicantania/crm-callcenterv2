<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\BusinessLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $saleDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $tramitatedAt = $this->faker->dateTimeBetween($saleDate, 'now');
        $liquidatedAt = $this->faker->optional()->dateTimeBetween($tramitatedAt, 'now');

        return [
            'company_name' => Company::inRandomOrder()->first()?->name,
            'cif' => Company::inRandomOrder()->first()?->cif,
            'address' => Company::inRandomOrder()->first()?->address,
            'city' => Company::inRandomOrder()->first()?->city,
            'province' => Company::inRandomOrder()->first()?->province,
            'phone' => Company::inRandomOrder()->first()?->phone,
            'email' => Company::inRandomOrder()->first()?->email,
            'activity' => Company::inRandomOrder()->first()?->activity,
            'cnae' => Company::inRandomOrder()->first()?->cnae,

            // Operador
            'operator_id' => User::whereHas('role', fn($q) => $q->where('name', 'Operador'))->inRandomOrder()->first()?->id,
            'sale_date' => $saleDate,

            // Producto y línea de negocio
            'product_id' => Product::inRandomOrder()->first()?->id,
            'business_line_id' => BusinessLine::inRandomOrder()->first()?->id,

            // Tramitador (administrador)
            'tramitator_id' => User::whereHas('role', fn($q) => $q->where('name', 'Administrador'))->inRandomOrder()->first()?->id,
            'processing_date' => $tramitatedAt,

            // Número de contrato y comisión
            'contract_number' => $this->faker->unique()->numerify('CTR-#####'),
            'commission_amount' => $this->faker->randomFloat(2, 50, 500),
            'commission_paid_date' => $liquidatedAt,

            // Usuario que liquida (gerencia)
            'liquidated_by' => User::whereHas('role', fn($q) => $q->where('name', 'Gerencia'))->inRandomOrder()->first()?->id,
            'liquidation_date' => $liquidatedAt,

            // Datos adicionales
            'legal_representative' => $this->faker->name,
            'legal_representative_dni' => $this->faker->unique()->numerify('DNI-#####'),
            'legal_representative_phone' => $this->faker->phoneNumber,
            'gestoria_cif' => $this->faker->unique()->numerify('GESTORIA-#####'),
            'gestoria_phone' => $this->faker->phoneNumber,
            'gestoria_email' => $this->faker->companyEmail,
            'student_dni' => $this->faker->unique()->numerify('STUDENT-#####'),
            'student_phone' => $this->faker->phoneNumber,
            'student_email' => $this->faker->email,
            'company_iban' => $this->faker->iban,
            'ss_company' => $this->faker->numerify('SS-#####'),
            'ss_student' => $this->faker->numerify('SS-#####'),

            // Estado
            'status' => $this->faker->randomElement(['pending', 'in_process', 'processed']),
        ];
    }
}
