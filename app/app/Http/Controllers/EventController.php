<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Filtri
        $q = trim((string) $request->query('q', ''));
        $associationId = $request->query('association_id');
        $region = trim((string) $request->query('region', ''));
        $type = trim((string) $request->query('type', ''));
        $from = $request->query('from');
        $to = $request->query('to');
        $showPast = $request->boolean('show_past', false);

        // Query base (adatta i nomi tabella/colonne se diversi)
        $eventsQuery = DB::table('events')
            ->leftJoin('associations', 'associations.id', '=', 'events.association_id')
            ->select([
                'events.id',
                'events.title',
                'events.type',
                'events.start_at',
                'events.city',
                'events.region',
                'events.status',
                'associations.name as association_name',
            ]);

        // Futuri di default
        if (!$showPast) {
            $eventsQuery->where(function ($w) {
                $w->whereNull('events.start_at')
                  ->orWhere('events.start_at', '>=', now());
            });
        }

        // Ricerca libera
        if ($q !== '') {
            $eventsQuery->where(function ($w) use ($q) {
                $w->whereILike('events.title', "%{$q}%")
                  ->orWhereILike('events.city', "%{$q}%")
                  ->orWhereILike('associations.name', "%{$q}%");
            });
        }

        // Filtri puntuali
        if (!empty($associationId)) {
            $eventsQuery->where('events.association_id', (int) $associationId);
        }

        if ($region !== '') {
            $eventsQuery->whereILike('events.region', "%{$region}%");
        }

        if ($type !== '') {
            $eventsQuery->where('events.type', $type);
        }

        // Date range (se presenti)
        if (!empty($from)) {
            $fromDt = Carbon::parse($from)->startOfDay();
            $eventsQuery->where('events.start_at', '>=', $fromDt);
        }

        if (!empty($to)) {
            $toDt = Carbon::parse($to)->endOfDay();
            $eventsQuery->where('events.start_at', '<=', $toDt);
        }

        // Ordinamento: futuri per data crescente, passati per data decrescente
        if ($showPast) {
            $eventsQuery->orderByDesc('events.start_at');
        } else {
            $eventsQuery->orderByRaw('events.start_at is null'); // null in fondo
            $eventsQuery->orderBy('events.start_at');
        }

        $events = $eventsQuery->paginate(12)->withQueryString();

        // Dati per filtri
        $associations = DB::table('associations')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Regioni disponibili (distinte)
        $regions = DB::table('events')
            ->select('region')
            ->whereNotNull('region')
            ->where('region', '<>', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        // Tipologie base (implementabile)
        $types = collect(['circolo', 'regionale', 'nazionale']);

        return view('events.index', [
            'events' => $events,
            'associations' => $associations,
            'regions' => $regions,
            'types' => $types,
            'filters' => [
                'q' => $q,
                'association_id' => $associationId,
                'region' => $region,
                'type' => $type,
                'from' => $from,
                'to' => $to,
                'show_past' => $showPast,
            ],
        ]);
    }

    // Lascia il tuo show se già implementato.
    // Qui lo lascio "placeholder" per evitare conflitti.
    public function show(Request $request, $eventId)
    {
        // Se il tuo show esiste già con logica tavoli/classifica, NON sostituirlo.
        // In caso contrario, dimmelo e lo ricreo allineato all'import.
        return redirect()->route('events.index');
    }
}
