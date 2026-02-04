@extends('layouts.app')

@section('content')
@php
  // stato toggle (passato dal controller) oppure da request
  $showPast = isset($showPast) ? (bool)$showPast : (bool)request()->boolean('show_past');

  // per mostrare "Filtri attivi"
  $hasFilters = collect([
    request('q'),
    request('association_id'),
    request('type'),
    request('region'),
    request('date_from'),
    request('date_to'),
    $showPast ? '1' : null,
  ])->filter(fn($v) => filled($v))->isNotEmpty();
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Eventi</h1>
      <p class="opacity-80 mt-1">
        {{ $showPast ? 'Tutti gli eventi (inclusi passati).' : 'Eventi futuri (default).' }}
      </p>
    </div>

    <div class="flex items-center gap-2">
      @if($hasFilters)
        <span class="badge badge-primary text-primary-content">Filtri attivi</span>
      @endif
      <a href="{{ route('events.index') }}" class="btn btn-outline whitespace-nowrap">Reset</a>
    </div>
  </div>

  {{-- Filtri --}}
  <div class="card bg-base-100 shadow">
    <div class="card-body space-y-4">

      <form method="GET" action="{{ route('events.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        {{-- Ricerca --}}
        <label class="form-control sm:col-span-4">
          <div class="label"><span class="label-text">Cerca</span></div>
          <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Nome evento, città…"
            class="input input-bordered w-full"
          />
        </label>

        {{-- Associazione --}}
        <label class="form-control">
          <div class="label"><span class="label-text">Associazione</span></div>
          <select name="association_id" class="select select-bordered w-full">
            <option value="">Tutte</option>
            @foreach($associations ?? [] as $a)
              <option value="{{ $a->id }}" @selected((string)request('association_id') === (string)$a->id)>
                {{ $a->name }}
              </option>
            @endforeach
          </select>
        </label>

        {{-- Tipologia --}}
        <label class="form-control">
          <div class="label"><span class="label-text">Tipologia</span></div>
          <select name="type" class="select select-bordered w-full">
            <option value="">Tutte</option>
            @foreach(['circolo' => 'Circolo', 'regionale' => 'Regionale', 'nazionale' => 'Nazionale'] as $k => $v)
              <option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </label>

        {{-- Regione --}}
        <label class="form-control">
          <div class="label"><span class="label-text">Regione</span></div>
          <input
            type="text"
            name="region"
            value="{{ request('region') }}"
            placeholder="Es. Lombardia"
            class="input input-bordered w-full"
          />
        </label>

        {{-- Data da --}}
        <label class="form-control">
          <div class="label"><span class="label-text">Dal</span></div>
          <input type="date" name="date_from" value="{{ request('date_from') }}" class="input input-bordered w-full" />
        </label>

        {{-- Data a --}}
        <label class="form-control">
          <div class="label"><span class="label-text">Al</span></div>
          <input type="date" name="date_to" value="{{ request('date_to') }}" class="input input-bordered w-full" />
        </label>

        {{-- Mostra passati --}}
        <div class="sm:col-span-2 flex items-end">
          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input
              type="checkbox"
              name="show_past"
              value="1"
              @checked($showPast)
              class="toggle toggle-primary bg-base-300 border border-base-300 checked:bg-primary checked:border-primary" >
            <span class="font-semibold">Mostra passati</span>
          </label>
        </div>

        {{-- CTA --}}
        <div class="sm:col-span-2 flex items-end justify-end gap-2">
          <button type="submit" class="btn btn-primary shadow whitespace-nowrap">
            Applica filtri
          </button>
        </div>

      </form>
    </div>
  </div>

  {{-- Lista eventi --}}
  @if($events->count() === 0)
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <h2 class="card-title">Nessun evento trovato</h2>
        <p class="opacity-80">
          Prova a rimuovere alcuni filtri oppure mostrare anche i passati.
        </p>
        <div class="card-actions justify-end">
          <a href="{{ route('events.index') }}" class="btn btn-outline">Reset filtri</a>
        </div>
      </div>
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
      @foreach($events as $e)
        @php
          $start = $e->start_at ? \Carbon\Carbon::parse($e->start_at) : null;
          $isPast = $start ? $start->isPast() : false;

          $typeLabel = match($e->type) {
            'circolo' => 'Circolo',
            'regionale' => 'Regionale',
            'nazionale' => 'Nazionale',
            default => $e->type ? ucfirst($e->type) : 'Evento'
          };
        @endphp

        <div class="card bg-base-100 shadow hover:shadow-lg transition">
          <div class="card-body">
            <div class="flex items-start justify-between gap-3">
              <h3 class="card-title leading-tight">
                {{ $e->title }}
              </h3>

              <span class="badge {{ $isPast ? 'badge-ghost' : 'badge-primary text-primary-content' }}">
                {{ $typeLabel }}
              </span>
            </div>

            <div class="space-y-1 opacity-80">
              <div>
                <span class="font-semibold">Associazione:</span>
                {{ $e->association_name ?? ($e->association->name ?? '-') }}
              </div>

              <div>
                <span class="font-semibold">Dove:</span>
                {{ $e->city ?? '-' }}{{ $e->region ? ', '.$e->region : '' }}
              </div>

              <div>
                <span class="font-semibold">Quando:</span>
                {{ $start ? $start->format('d/m/Y H:i') : '-' }}
                @if($isPast)
                  <span class="text-sm opacity-70">· concluso</span>
                @endif
              </div>
            </div>

            <div class="card-actions justify-end mt-2">
              <a href="{{ route('events.show', $e->id) }}" class="btn btn-primary shadow whitespace-nowrap">
                Apri evento →
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Paginazione --}}
    <div class="flex justify-center">
      {{ $events->links() }}
    </div>
  @endif

</div>
@endsection
