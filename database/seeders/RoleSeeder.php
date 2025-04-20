<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Operador', 'description' => 'Responsable de realizar llamadas y registrar ventas'],
            ['name' => 'Administrador', 'description' => 'Encargado de revisar, corregir y tramitar las ventas'],
            ['name' => 'Gerencia', 'description' => 'Accede a estadísticas y reportes de rendimiento'],
            ['name' => 'Superadmin', 'description' => 'Acceso total a la plataforma y administración global'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], ['description' => $role['description']]);
        }
    }
}
