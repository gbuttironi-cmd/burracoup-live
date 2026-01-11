@extends('layouts.app')

@section('content')
@php
  $q = $filters['q'] ?? '';
  $associationId = $filters['association_id'] ?? '';
  $region = $filters['region'] ?? '';
  $type = $filters['type'] ?? '';
  $from = $filters['from'] ?? '';
  $to = $filters['to'] ?? '';
  $showPast = (bool)($filters['show_past'] ?? false);
@endphp

<div class="space-y-6">

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Eventi</h1>
          <p class="text-base-content/70 mt-1">
            Futuri di default. Attiva “Mostra passati” per vedere lo storico.
          </p>
        </div>

        <div class="flex gap-2">
          <a href="{{ route('events.index') }}" class="btn btn-outline">Reset</a>
        </div>
      </div>

      <form method="GET" class="mt-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <label class="form-control sm:col-span-4">
          <div class="label"><span class="label-text">Cerca</span></div>
          <input class="input input-bordered w-full"
                 name="q"
                 value="{{ $q }}"
                 placeholder="Titolo, città o associazione…">
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Associazione</span></div>
          <select class="select select-bordered" name="association_id">
            <option value="">Tutte</option>
            @foreach($associations as $a)
              <option value="{{ $a->id }}" @selected((string)$associationId === (string)$a->id)>{{ $a->name }}</option>
            @endforeach
          </select>
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Regione</span></div>
          <select class="select select-bordered" name="region">
            <option value="">Tutte</option>
            @foreach($regions as $r)
              <option value="{{ $r }}" @selected((string)$region === (string)$r)>{{ $r }}</option>
            @endforeach
          </select>
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Tipologia</span></div>
          <select class="select select-bordered" name="type">
            <option value="">Tutte</option>
            @foreach($types as $t)
              <option value="{{ $t }}" @selected((string)$type === (string)$t)>{{ ucfirst($t) }}</option>
            @endforeach
          </select>
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Date</span></div>
          <div class="flex gap-2">
            <input type="date" class="input input-bordered w-full" name="from" value="{{ $from }}">
            <input type="date" class="input input-bordered w-full" name="to" value="{{ $to }}">
          </div>
        </label>

        <div class="sm:col-span-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-1">
          <label class="label cursor-pointer gap-3 justify-start">
            <input type="checkbox" class="toggle toggle-primary" name="show_past" value="1" @checked($showPast)>
            <span class="label-text font-semibold">Mostra passati</span>
          </label>

          <button class="btn btn-primary text-white" type="submit">Applica filtri</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Lista eventi --}}
  @if($events->count() === 0)
    <div class="alert bg-base-100 shadow border">
      <span>Nessun evento trovato con questi filtri.</span>
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
      @foreach($events as $e)
        <div class="card bg-base-100 shadow hover:shadow-lg transition">
          <div class="card-body">
            <div class="flex items-start justify-between gap-3">
              <h2 class="card-title leading-tight">{{ $e->title }}</h2>
              @if($e->type)
                <span class="badge badge-primary">{{ ucfirst($e->type) }}</span>
              @endif
            </div>

            <div class="text-sm text-base-content/70 space-y-1 mt-1">
              <div><span class="font-semibold">Associazione:</span> {{ $e->association_name ?? '-' }}</div>
              <div>
                <span class="font-semibold">Quando:</span>
                @if($e->start_at)
                  {{ \Carbon\Carbon::parse($e->start_at)->format('d/m/Y H:i') }}
                @else
                  Da definire
                @endif
              </div>
              <div>
                <span class="font-semibold">Dove:</span>
                {{ $e->city ?? '-' }}{{ $e->region ? ', '.$e->region : '' }}
              </div>
            </div>

            <div class="card-actions justify-end mt-3">
              <a class="btn btn-primary text-white" href="{{ route('events.show', $e->id) }}?tab=tables">
                Apri evento
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4">
      {{ $events->links() }}
    </div>
  @endif

</div>
@endsection
