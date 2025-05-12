<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'password' => Hash::make('1234'),
            'role_id' => 4,
        ]);

        // 2 tramitadores
        foreach (range(1, 2) as $i) {
            User::updateOrCreate([
                'email' => "admin$i@crm.com",
            ], [
                'name' => "Admin $i",
                'last_name' => "Apellidos",
                'middle_name' => "Admin$i",
                'password' => Hash::make('1234'),
                'role_id' => 2,
            ]);
        }

        // 1 gerente
        User::updateOrCreate([
            'email' => 'gerente@crm.com',
        ], [
            'name' => 'Gerente',
            'last_name' => 'Apellidos',
            'middle_name' => 'Responsable',
            'password' => Hash::make('1234'),
            'role_id' => 3,
        ]);

        // 25 operadores
        foreach (range(1, 25) as $i) {
            User::updateOrCreate([
                'email' => "operador$i@crm.com",
            ], [
                'name' => "Operador $i",
                'last_name' => "Apellidos",
                'middle_name' => "Operador$i",
                'password' => Hash::make('1234'),
                'role_id' => 1,
            ]);
        }
    }
}
