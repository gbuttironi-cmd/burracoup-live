<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventStandingsSeeder extends Seeder
{
    public function run(): void
    {
        $events = DB::table('public.events')->orderBy('id')->get(['id']);

        foreach ($events as $event) {
            $rounds = DB::table('public.event_rounds')
                ->where('event_id', $event->id)
                ->orderBy('round_no')
                ->get(['id', 'round_no']);

            foreach ($rounds as $round) {

                DB::table('public.event_standings')
                    ->where('event_id', $event->id)
                    ->where('round_id', $round->id)
                    ->delete();

                $rows = [
                    ['rank' => 1, 'competitor_name' => 'VISMARA VERDIANA - MARTINENGO MARIA TERESA', 'points' => 52],
                    ['rank' => 2, 'competitor_name' => 'ROTA FABIO - PAVONI ROBERTO', 'points' => 47],
                    ['rank' => 3, 'competitor_name' => 'SILVESTRI ALESSANDRA - CAPPONI STEFANO', 'points' => 44],
                    ['rank' => 4, 'competitor_name' => 'PEDRONI MARINA - MENDES DA SILVA ELIANA', 'points' => 41],
                ];

                $payload = array_map(fn ($r) => [
                    'event_id' => $event->id,
                    'round_id' => $round->id,
                    'rank' => $r['rank'],
                    'competitor_name' => $r['competitor_name'],
                    'points' => $r['points'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $rows);

                DB::table('public.event_standings')->insert($payload);
            }
        }
    }
}
