<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
            BusinessLineSeeder::class,
            CallSeeder::class,
            ProductSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
