<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminImportController extends Controller
{
    public function index(Request $request)
    {
        $events = DB::table('events')->orderByDesc('start_at')->get(['id', 'title']);
        $eventId = (int) ($request->query('event_id') ?? ($events->first()->id ?? 0));

        $rounds = $eventId
            ? DB::table('event_rounds')
                ->where('event_id', $eventId)
                ->orderBy('round_no')
                ->get(['id', 'round_no', 'name', 'status'])
            : collect();

        return view('admin.import', compact('events', 'eventId', 'rounds'));
    }

    public function importTables(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer'],
            'round_id' => ['required', 'integer'],
            'mode' => ['required', 'in:danese_two_rows,by_table,alphabetical'],
            'payload' => ['required', 'string'],
            'source_name' => ['nullable', 'string', 'max:255'],
        ]);

        $eventId = (int) $data['event_id'];
        $roundId = (int) $data['round_id'];
        $mode = $data['mode'];
        $payload = trim($data['payload']);

        // Audit import
        $importId = DB::table('event_imports')->insertGetId([
            'event_id' => $eventId,
            'source_type' => 'paste',
            'source_name' => $data['source_name'] ?: 'incolla',
            'import_kind' => 'tables',
            'imported_by' => 'admin-key',
            'raw_payload' => json_encode([
                'mode' => $mode,
                'payload' => $payload,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Linee pulite
        $lines = preg_split("/\r\n|\r|\n/", $payload);
        $rows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') $rows[] = $line;
        }

        if (count($rows) === 0) {
            return back()->with('error', 'Nessuna riga valida trovata.');
        }

        // Import “semplice e affidabile”: cancella e reinserisci per quel turno
        DB::table('event_tables')
            ->where('event_id', $eventId)
            ->where('round_id', $roundId)
            ->delete();

        $inserts = [];
        $errors = [];

        if ($mode === 'danese_two_rows') {
            /**
             * Formato atteso (TAB separato), con eventuali colonne extra IGNORATE:
             * GIRONE    TAVOLO    COPPIA
             * (2 righe per tavolo: prima F poi M)
             */
            $byKey = []; // key = group|table

            foreach ($rows as $i => $line) {
                $parts = $this->splitLine($line);

                // Prendiamo SOLO le prime 3 colonne
                $group = trim((string)($parts[0] ?? ''));
                $tableRaw = (string)($parts[1] ?? '');
                $pair = trim((string)($parts[2] ?? ''));

                $tableNo = (int) preg_replace('/\D+/', '', $tableRaw);

                if ($group === '' || $tableNo <= 0 || $pair === '') {
                    $errors[] = "Riga ".($i+1).": dati non validi (atteso: GIRONE, TAVOLO, COPPIA)";
                    continue;
                }

                $key = $group.'|'.$tableNo;

                if (!isset($byKey[$key])) {
                    $byKey[$key] = [
                        'event_id' => $eventId,
                        'round_id' => $roundId,
                        'group_code' => $group,
                        'import_id' => $importId,
                        'table_no' => $tableNo,
                        'fixed_pair_name' => null,
                        'mobile_pair_name' => null,
                        'pair_a_name' => null,
                        'pair_b_name' => null,
                        'extra' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // prima occorrenza = F, seconda = M
                if ($byKey[$key]['fixed_pair_name'] === null) {
                    $byKey[$key]['fixed_pair_name'] = $pair;
                } elseif ($byKey[$key]['mobile_pair_name'] === null) {
                    $byKey[$key]['mobile_pair_name'] = $pair;
                } else {
                    $errors[] = "Riga ".($i+1).": terza coppia per Girone {$group} Tavolo {$tableNo} (ignorata)";
                }
            }

            $inserts = array_values($byKey);

        } elseif ($mode === 'by_table') {
            // Formato flessibile:
            // 1; CoppiaA; CoppiaB
            foreach ($rows as $i => $line) {
                $parts = $this->splitLine($line);
                if (count($parts) < 2) {
                    $errors[] = "Riga ".($i+1).": formato non valido";
                    continue;
                }

                $tableNo = (int) preg_replace('/\D+/', '', (string)$parts[0]);
                if ($tableNo <= 0) {
                    $errors[] = "Riga ".($i+1).": numero tavolo non valido";
                    continue;
                }

                $a = trim((string)($parts[1] ?? ''));
                $b = trim((string)($parts[2] ?? ''));

                if ($a === '') {
                    $errors[] = "Riga ".($i+1).": coppia A mancante";
                    continue;
                }

                $inserts[] = [
                    'event_id' => $eventId,
                    'round_id' => $roundId,
                    'group_code' => null,
                    'import_id' => $importId,
                    'table_no' => $tableNo,
                    'fixed_pair_name' => null,
                    'mobile_pair_name' => null,
                    'pair_a_name' => $a,
                    'pair_b_name' => $b !== '' ? $b : null,
                    'extra' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

        } else {
            // alphabetical: "Coppia; Tavolo" (colonne extra ignorate)
            foreach ($rows as $i => $line) {
                $parts = $this->splitLine($line);

                $pair = trim((string)($parts[0] ?? ''));
                $tableRaw = (string)($parts[1] ?? '');
                $tableNo = (int) preg_replace('/\D+/', '', $tableRaw);

                if ($pair === '' || $tableNo <= 0) {
                    $errors[] = "Riga ".($i+1).": formato non valido (atteso: COPPIA, TAVOLO)";
                    continue;
                }

                $inserts[] = [
                    'event_id' => $eventId,
                    'round_id' => $roundId,
                    'group_code' => null,
                    'import_id' => $importId,
                    'table_no' => $tableNo,
                    'fixed_pair_name' => null,
                    'mobile_pair_name' => null,
                    'pair_a_name' => $pair,
                    'pair_b_name' => null,
                    'extra' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($inserts) > 0) {
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('event_tables')->insert($chunk);
            }
        }

        return redirect()
            ->route('admin.import', ['event_id' => $eventId, 'key' => $request->query('key')])
            ->with('success', 'Import completato. Record creati: '.count($inserts).($errors ? ' | Avvisi: '.count($errors) : ''))
            ->with('import_errors', $errors);
    }

    private function splitLine(string $line): array
    {
        // Supporta TAB (tuo caso), ;, ,
        if (str_contains($line, "\t")) {
            return array_map('trim', explode("\t", $line));
        }
        if (str_contains($line, ';')) {
            return array_map('trim', explode(';', $line));
        }
        if (str_contains($line, ',')) {
            return array_map('trim', explode(',', $line));
        }
        // fallback
        return preg_split('/\s{2,}/', $line) ?: [$line];
    }
}
