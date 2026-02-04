<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Associazione base: se non esiste ne crea una
        $assocId = DB::table('public.associations')->value('id');

        if (!$assocId) {
            $assocId = DB::table('public.associations')->insertGetId([
                'name' => 'BurracoUP Test Club',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $events = [
            [
                'title' => 'Torneo Test – Circolo (BG)',
                'association_id' => $assocId,
                'type' => 'circolo',
                'region' => 'Lombardia',
                'city' => 'Bergamo',
                'address' => 'Via Roma 1, Bergamo',
                'lat' => 45.6983,
                'lng' => 9.6773,
                'start_at' => now()->addDays(7)->setTime(14, 30),
                'end_at' => now()->addDays(7)->setTime(20, 0),
                // JSON: struttura semplice e utile
                'info_pre_evento' => [
                    'ritrovo' => '14:00',
                    'iscrizioni_entro' => '14:15',
                    'note' => [
                        'Premi ai primi 3',
                        'Si gioca Danese',
                    ],
                    'convenzioni' => [
                        ['tipo' => 'hotel', 'nome' => 'Hotel XYZ', 'note' => '10% sconto mostrando locandina'],
                        ['tipo' => 'ristorante', 'nome' => 'Trattoria ABC', 'note' => 'Menu convenzionato'],
                    ],
                ],
                'status' => 'published',
            ],
            [
                'title' => 'Torneo Test – Regionale (MI)',
                'association_id' => $assocId,
                'type' => 'regionale',
                'region' => 'Lombardia',
                'city' => 'Milano',
                'address' => 'Piazza Duomo, Milano',
                'lat' => 45.4642,
                'lng' => 9.1900,
                'start_at' => now()->addDays(14)->setTime(15, 0),
                'end_at' => now()->addDays(14)->setTime(21, 0),
                'info_pre_evento' => [
                    'note' => [
                        'Parcheggio consigliato: P1',
                        'Premi ai primi 5',
                    ],
                    'contatti' => [
                        ['label' => 'Telefono', 'value' => '+39 000 0000000'],
                        ['label' => 'Email', 'value' => 'info@burracoup.test'],
                    ],
                ],
                'status' => 'draft',
            ],
            [
                'title' => 'Torneo Test – Nazionale (RM)',
                'association_id' => $assocId,
                'type' => 'nazionale',
                'region' => 'Lazio',
                'city' => 'Roma',
                'address' => 'Via del Corso 10, Roma',
                'lat' => 41.9028,
                'lng' => 12.4964,
                'start_at' => now()->addDays(30)->setTime(10, 0),
                'end_at' => now()->addDays(30)->setTime(19, 0),
                'info_pre_evento' => [
                    'accredito' => '09:15',
                    'pausa_pranzo' => '13:00',
                    'finale_prevista' => '18:30',
                    'note' => ['Portare tessera'],
                ],
                'status' => 'published',
            ],
        ];

        foreach ($events as $e) {
            DB::table('public.events')->updateOrInsert(
                ['title' => $e['title']],
                [
                    'association_id' => $e['association_id'],
                    'type' => $e['type'],
                    'region' => $e['region'],
                    'city' => $e['city'],
                    'address' => $e['address'],
                    'lat' => $e['lat'],
                    'lng' => $e['lng'],
                    'start_at' => $e['start_at'],
                    'end_at' => $e['end_at'],
                    // forza JSON valido per Postgres
                    'info_pre_evento' => json_encode($e['info_pre_evento'], JSON_UNESCAPED_UNICODE),
                    'status' => $e['status'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
