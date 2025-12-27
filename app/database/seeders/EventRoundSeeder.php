<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventRoundSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = DB::table('events')->value('id');

        DB::table('event_rounds')->insert([
            [
                'event_id' => $eventId,
                'round_no' => 1,
                'name' => 'Turno 1',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $eventId,
                'round_no' => 2,
                'name' => 'Turno 2',
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
