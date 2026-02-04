<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRound;
use App\Models\EventTable;
use App\Models\EventStanding;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // per ora lo lasciamo com'è (tu ce l’hai già funzionante)
        return redirect()->route('events.index');
    }

    public function show(Event $event, Request $request)
    {
        $tab = $request->string('tab')->toString() ?: 'tables'; // tables|standings
        $view = $request->string('view')->toString() ?: 'tables'; // tables|alpha (solo tab=tables)

        $rounds = EventRound::query()
            ->where('event_id', $event->id)
            ->orderBy('round_no')
            ->get();

        $activeRoundId = (int)($request->input('round_id') ?: ($rounds->first()->id ?? 0));
        if ($activeRoundId === 0) {
            // nessun turno -> vista vuota ma pagina ok
            return view('events.show', [
                'event' => $event,
                'tab' => $tab,
                'view' => $view,
                'rounds' => $rounds,
                'activeRoundId' => null,
                'tables' => collect(),
                'alphaRows' => collect(),
                'standings' => collect(),
            ]);
        }

        // Tavoli (1 riga per tavolo: fixed_pair_name + mobile_pair_name)
        $tables = EventTable::query()
            ->where('event_id', $event->id)
            ->where('round_id', $activeRoundId)
            ->orderBy('group_code')
            ->orderBy('table_no')
            ->get();

        // Vista alfabetica: una riga per coppia (Fisso + Mobile), ordinata per nome coppia
        $alphaRows = $tables
            ->flatMap(function ($t) {
                $out = [];

                if (!empty($t->fixed_pair_name)) {
                    $out[] = (object)[
                        'pair_name' => $t->fixed_pair_name,
                        'label' => 'Fisso',
                        'table_no' => $t->table_no,
                    ];
                }

                if (!empty($t->mobile_pair_name)) {
                    $out[] = (object)[
                        'pair_name' => $t->mobile_pair_name,
                        'label' => 'Mobile',
                        'table_no' => $t->table_no,
                    ];
                }

                return $out;
            })
            ->sortBy(fn($r) => mb_strtoupper($r->pair_name ?? ''))
            ->values();

        // Classifica (se non c’è ancora il model/tabella, resta vuota e non rompe)
        $standings = collect();
        if ($tab === 'standings' && class_exists(EventStanding::class)) {
            $standings = EventStanding::query()
                ->where('event_id', $event->id)
                ->where('round_id', $activeRoundId)
                ->orderBy('rank')
                ->get();
        }

        return view('events.show', [
            'event' => $event,
            'tab' => $tab,
            'view' => $view,
            'rounds' => $rounds,
            'activeRoundId' => $activeRoundId,
            'tables' => $tables,
            'alphaRows' => $alphaRows,
            'standings' => $standings,
        ]);
    }
}
