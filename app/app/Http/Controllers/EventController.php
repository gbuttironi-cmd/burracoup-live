<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $region = trim((string) $request->query('region', ''));
        $type = trim((string) $request->query('type', ''));
        $associationId = $request->query('association_id');

        $events = DB::table('events')
            ->join('associations', 'associations.id', '=', 'events.association_id')
            ->select(['events.*', 'associations.name as association_name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('events.title', 'ilike', "%{$q}%")
                       ->orWhere('events.city', 'ilike', "%{$q}%")
                       ->orWhere('associations.name', 'ilike', "%{$q}%");
                });
            })
            ->when($region !== '', fn($query) => $query->where('events.region', $region))
            ->when($type !== '', fn($query) => $query->where('events.type', $type))
            ->when($associationId, fn($query) => $query->where('events.association_id', (int)$associationId))
            ->orderByRaw("CASE WHEN events.status='published' THEN 0 ELSE 1 END")
            ->orderBy('events.start_at', 'asc')
            ->paginate(12)
            ->withQueryString();

        $associations = DB::table('associations')->orderBy('name')->get(['id', 'name']);
        $regions = DB::table('events')->whereNotNull('region')->distinct()->orderBy('region')->pluck('region');
        $types = DB::table('events')->distinct()->orderBy('type')->pluck('type');

        return view('events.index', compact('events', 'associations', 'regions', 'types', 'q', 'region', 'type', 'associationId'));
    }

    public function show(Request $request, int $eventId)
    {
        $event = DB::table('events')
            ->join('associations', 'associations.id', '=', 'events.association_id')
            ->where('events.id', $eventId)
            ->select(['events.*', 'associations.name as association_name'])
            ->first();

        abort_if(!$event, 404);

        $tab = $request->query('tab', 'tables'); // tables|standings
        $roundId = $request->query('round_id');

        $rounds = DB::table('event_rounds')
            ->where('event_id', $eventId)
            ->orderBy('round_no')
            ->get();

        $defaultRoundId = $rounds->firstWhere('status', 'published')->id ?? ($rounds->first()->id ?? null);
        $activeRoundId = $roundId ? (int)$roundId : $defaultRoundId;

        $tables = collect();
        if ($activeRoundId) {
            $tables = DB::table('event_tables')
                ->where('event_id', $eventId)
                ->where('round_id', $activeRoundId)
                ->orderBy('group_code')
                ->orderBy('table_no')
                ->get();
        }

        // Righe alfabetiche: solo coppia, ordinata per primo giocatore (prima di " - ")
		// Righe alfabetiche: solo coppia, ordinata per "pair_name"
		$alphaRows = $tables->flatMap(function ($t) {
			$out = [];

			foreach (
				[
					['label' => 'Fisso',  'name' => $t->fixed_pair_name],
					['label' => 'Mobile', 'name' => $t->mobile_pair_name],
				] as $p
			) {
				if (!$p['name']) continue;

				$out[] = (object) [
					'pair_name' => $p['name'],
					'label' => $p['label'],
					'table_no' => $t->table_no,
				];
			}

			return $out;
		})->sortBy('pair_name')->values();


        $standings = DB::table('event_standings')
            ->where('event_id', $eventId)
            ->when($tab === 'standings' && $activeRoundId, fn($q) => $q->where('round_id', $activeRoundId))
            ->orderByRaw('rank IS NULL, rank ASC')
            ->limit(500)
            ->get();

        return view('events.show', compact('event', 'tab', 'rounds', 'activeRoundId', 'tables', 'standings', 'alphaRows'));
    }
}
