<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BusinessLine;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(20)->create()->each(function ($user) {
            $lineas = BusinessLine::inRandomOrder()->take(rand(1, 2))->pluck('id');
            $user->businessLines()->sync($lineas);
        });
    }
}
