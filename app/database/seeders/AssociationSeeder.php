<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssociationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('associations')->insert([
            'name' => 'BurracoUP',
            'region' => 'Emilia-Romagna',
            'city' => 'Riccione',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
