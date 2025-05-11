<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            BusinessLineSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
            CallSeeder::class,
            ProductSeeder::class,
            SaleSeeder::class,
        ]);

        // ✅ Creamos Superadmin rellenando los campos obligatorios
        User::updateOrCreate(
            ['email' => 'superadmin@crm.com'],
            [
                'name' => 'Super',
                'last_name' => 'Admin',
                'middle_name' => 'CRM', // puedes poner CRM o lo que quieras
                'identification_number' => '00000000A', // un DNI falso para el seed
                'password' => bcrypt('1234'),
                'role_id' => 4, // Superadmin
                'active' => true,
            ]
        );
    }
}
