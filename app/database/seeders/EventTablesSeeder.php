<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTablesSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = DB::table('public.events')->min('id');
        if (!$eventId) {
            $this->command?->warn('Nessun evento trovato. Esegui prima EventSeeder.');
            return;
        }

        $roundId = DB::table('public.event_rounds')
            ->where('event_id', $eventId)
            ->orderBy('round_no')
            ->value('id');

        if (!$roundId) {
            $this->command?->warn('Nessun round trovato. Esegui prima EventRoundSeeder.');
            return;
        }

        // Esempio: 2 righe per tavolo (prima F poi M)
        $rows = [
            ["A", 1, "VISMARA VERDIANA - MARTINENGO MARIA TERESA"],
            ["A", 1, "PEDRONI MARINA - MENDES DA SILVA ELIANA"],
            ["A", 2, "PEZZOTTA SERENELLA - FACHERIS IVANA"],
            ["A", 2, "ROTA FABIO - PAVONI ROBERTO"],
            ["A", 3, "GIANTOMASO FRANCESCA - BARDI DIANORA"],
            ["A", 3, "ZANCHI LUCIA - BELLATTI ANTONIA"],
            ["A", 4, "MARTINEZ CORINNE - CROTTI MARILISA"],
            ["A", 4, "PAGANESSI DINA - ROTA AGNESE"],
            ["A", 5, "SILVESTRI ALESSANDRA - CAPPONI STEFANO"],
            ["A", 5, "MAZZOLENI BARBARA - MAZZOLENI ERIKA"],
            ["A", 6, "DONADONI BARBARA - CROTTI BIANCAROSA"],
            ["A", 6, "BELLI AMELIA - MANZONI GIULIANO ETTORE"],
        ];

        // Costruiamo 1 record per tavolo: la prima riga è F, la seconda M
        $byTable = [];
        foreach ($rows as $r) {
            [$groupCode, $tableNo, $pair] = $r;

            // ignora righe sporche
            $groupCode = trim((string)$groupCode);
            $pair = trim((string)$pair);
            $tableNo = (int)$tableNo;

            if ($groupCode === '' || $tableNo <= 0 || $pair === '') {
                continue;
            }

            $key = $groupCode . '|' . $tableNo;

            if (!isset($byTable[$key])) {
                $byTable[$key] = [
                    'group_code' => $groupCode,
                    'table_no' => $tableNo,
                    'fixed_pair_name' => null,
                    'mobile_pair_name' => null,
                ];
            }

            // Regola: sempre 2 righe per tavolo → prima F poi M
            if ($byTable[$key]['fixed_pair_name'] === null) {
                $byTable[$key]['fixed_pair_name'] = $pair;
            } else {
                $byTable[$key]['mobile_pair_name'] = $pair;
            }
        }

        // Scrittura idempotente: una riga per (event_id, round_id, group_code, table_no)
        foreach ($byTable as $t) {
            DB::table('public.event_tables')->updateOrInsert(
                [
                    'event_id' => $eventId,
                    'round_id' => $roundId,
                    'group_code' => $t['group_code'],
                    'table_no' => $t['table_no'],
                ],
                [
                    'import_id' => null,          // per ora
                    'fixed_pair_name' => $t['fixed_pair_name'],
                    'mobile_pair_name' => $t['mobile_pair_name'],
                    'pair_a_name' => null,        // per ora (se vuoi lo mappiamo dopo)
                    'pair_b_name' => null,        // per ora
                    'extra' => json_encode(['seed' => true], JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
