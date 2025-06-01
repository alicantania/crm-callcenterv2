<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class FillMissingCallTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        DB::table('calls')
            ->whereNull('call_time')
            ->orderBy('id')
            ->chunkById(500, function ($calls) use ($faker) {
                foreach ($calls as $call) {
                    DB::table('calls')
                        ->where('id', $call->id)
                        ->update([
                            'call_time' => $faker->time('H:i:s'),
                        ]);
                }
            });
    }
}
