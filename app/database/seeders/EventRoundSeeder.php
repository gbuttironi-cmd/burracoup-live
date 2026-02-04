<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventRoundSeeder extends Seeder
{
    public function run(): void
    {
        $eventIds = DB::table('public.events')->pluck('id');

        foreach ($eventIds as $eventId) {
            $rounds = [
                ['event_id' => $eventId, 'round_no' => 1, 'name' => 'Turno 1', 'status' => 'published'],
                ['event_id' => $eventId, 'round_no' => 2, 'name' => 'Turno 2', 'status' => 'draft'],
            ];

            foreach ($rounds as $r) {
                DB::table('public.event_rounds')->updateOrInsert(
                    ['event_id' => $r['event_id'], 'round_no' => $r['round_no']], // match col vincolo unico
                    [
                        'name' => $r['name'],
                        'status' => $r['status'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
