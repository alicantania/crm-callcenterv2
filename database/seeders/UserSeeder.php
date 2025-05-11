<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        User::updateOrCreate([
            'email' => 'superadmin@crm.com',
        ], [
            'name' => 'Superadmin',
            'last_name' => 'CRM',
            'middle_name' => 'CallCenter',
            'email' => 'superadmin@crm.com',
            'password' => bcrypt('1234'),
            'role_id' => 4,
        ]);

        // 2 tramitadores
        foreach (range(1, 2) as $i) {
            User::create([
                'name' => "Admin $i",
                'last_name' => "Apellidos",
                'middle_name' => "Admin$i",
                'email' => "admin$i@crm.com",
                'password' => bcrypt('1234'),
                'role_id' => 2,
            ]);
        }

        // 1 gerente
        User::create([
            'name' => 'Gerente',
            'last_name' => 'Apellidos',
            'middle_name' => 'Responsable',
            'email' => 'gerente@crm.com',
            'password' => bcrypt('1234'),
            'role_id' => 3,
        ]);

        // 25 operadores
        foreach (range(1, 25) as $i) {
            User::create([
                'name' => "Operador $i",
                'last_name' => "Apellidos",
                'middle_name' => "Operador$i",
                'email' => "operador$i@crm.com",
                'password' => bcrypt('1234'),
                'role_id' => 1,
            ]);
        }
    }
}
