<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $associationId = DB::table('associations')->value('id');

        DB::table('events')->insert([
            'association_id' => $associationId,
            'title' => 'Torneo Regionale BurracoUP',
            'type' => 'regionale',
            'region' => 'Emilia-Romagna',
            'city' => 'Riccione',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(6),
            'address' => 'Palacongressi di Riccione',
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
