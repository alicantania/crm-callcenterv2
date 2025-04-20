<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Call;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        Call::factory()->count(100)->create();
    }
}
