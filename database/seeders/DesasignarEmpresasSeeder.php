<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class DesasignarEmpresasSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()
            ->whereNotNull('assigned_operator_id')
            ->inRandomOrder()
            ->take(10)
            ->update(['assigned_operator_id' => null]);

        $this->command->info('✅ 10 empresas han sido desasignadas y vuelven a la bolsa común.');
    }
}
