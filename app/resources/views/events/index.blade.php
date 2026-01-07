@extends('layouts.app')

@section('content')
<div class="space-y-6">

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold">Eventi</h1>
          <p class="text-base-content/70">Cerca e apri l’evento per vedere tavoli e classifiche.</p>
        </div>
        <a href="/" class="btn btn-ghost">Home</a>
      </div>

      <form method="GET" class="mt-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <label class="form-control">
          <div class="label"><span class="label-text">Cerca</span></div>
          <input
            name="q"
            value="{{ $q }}"
            class="input input-bordered w-full {{ $q ? 'input-primary' : '' }}"
            placeholder="Titolo, città, associazione">
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Associazione</span></div>
          <select name="association_id" class="select select-bordered w-full {{ $associationId ? 'select-primary' : '' }}">
            <option value="">Tutte</option>
            @foreach($associations as $a)
              <option value="{{ $a->id }}" @selected((string)$associationId === (string)$a->id)>{{ $a->name }}</option>
            @endforeach
          </select>
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Regione</span></div>
          <select name="region" class="select select-bordered w-full {{ $region ? 'select-primary' : '' }}">
            <option value="">Tutte</option>
            @foreach($regions as $r)
              <option value="{{ $r }}" @selected($region === $r)>{{ $r }}</option>
            @endforeach
          </select>
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Tipologia</span></div>
          <select name="type" class="select select-bordered w-full {{ $type ? 'select-primary' : '' }}">
            <option value="">Tutte</option>
            @foreach($types as $t)
              <option value="{{ $t }}" @selected($type === $t)>{{ ucfirst($t) }}</option>
            @endforeach
          </select>
        </label>

        <div class="sm:col-span-4 flex flex-wrap gap-2">
          <button class="btn btn-primary">Filtra</button>
          <a class="btn btn-outline" href="{{ route('events.index') }}">Reset</a>
        </div>
      </form>

      @php
        $hasFilters = ($q !== '') || ($region !== '') || ($type !== '') || !empty($associationId);
      @endphp

      @if($hasFilters)
        <div class="mt-4 flex flex-wrap gap-2 items-center">
          <span class="text-sm text-base-content/70">Filtri attivi:</span>
          @if($q) <span class="badge badge-primary">Cerca: {{ $q }}</span> @endif
          @if($associationId)
            @php $an = optional($associations->firstWhere('id', (int)$associationId))->name; @endphp
            <span class="badge badge-primary">Associazione: {{ $an ?: $associationId }}</span>
          @endif
          @if($region) <span class="badge badge-primary">Regione: {{ $region }}</span> @endif
          @if($type) <span class="badge badge-primary">Tipo: {{ ucfirst($type) }}</span> @endif
        </div>
      @endif

    </div>
  </div>

  <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($events as $e)
      <a href="{{ route('events.show', $e->id) }}" class="card bg-base-100 shadow hover:shadow-lg transition">
        <div class="card-body">
          <div class="flex items-start justify-between gap-3">
            <h2 class="card-title leading-tight">{{ $e->title }}</h2>
            <span class="badge {{ $e->status === 'published' ? 'badge-success' : 'badge-ghost' }}">
              {{ $e->status }}
            </span>
          </div>

          <div class="text-sm text-base-content/70 space-y-1">
            <div><span class="font-semibold">Associazione:</span> {{ $e->association_name }}</div>
            <div><span class="font-semibold">Tipo:</span> {{ ucfirst($e->type) }}</div>
            <div><span class="font-semibold">Luogo:</span> {{ $e->city ?? '-' }}{{ $e->region ? ', '.$e->region : '' }}</div>
            <div><span class="font-semibold">Data:</span>
              {{ $e->start_at ? \Carbon\Carbon::parse($e->start_at)->format('d/m/Y H:i') : '-' }}
            </div>
          </div>

          <div class="card-actions justify-end mt-2">
            <span class="btn btn-sm btn-primary">Apri</span>
          </div>
        </div>
      </a>
    @empty
      <div class="md:col-span-2 xl:col-span-3">
        <div class="alert">
          <span>Nessun evento trovato. Prova a cambiare i filtri.</span>
        </div>
      </div>
    @endforelse
  </div>

  <div>
    {{ $events->links() }}
  </div>

</div>
@endsection
