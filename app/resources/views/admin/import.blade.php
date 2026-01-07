@extends('layouts.app')

@section('content')
<div class="space-y-6">

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h1 class="card-title">Admin Import</h1>
      <p class="text-base-content/70">
        Import manuale (incolla) — protetto da Admin Key.
      </p>

      @if(session('success'))
        <div class="alert alert-success mt-3">
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-error mt-3">
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <form method="GET" class="mt-4 flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="key" value="{{ request('key') }}">

        <label class="form-control w-full sm:max-w-xl">
          <div class="label"><span class="label-text">Evento</span></div>
          <select name="event_id" class="select select-bordered" onchange="this.form.submit()">
            @foreach($events as $e)
              <option value="{{ $e->id }}" @selected((int)$eventId === (int)$e->id)>{{ $e->title }}</option>
            @endforeach
          </select>
        </label>
      </form>
    </div>
  </div>

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title">Import posizione ai tavoli</h2>

      <form method="POST" action="{{ route('admin.import.tables', ['key' => request('key')]) }}" class="mt-3 space-y-4">
        @csrf

        <input type="hidden" name="event_id" value="{{ $eventId }}">

        <div class="grid sm:grid-cols-2 gap-3">
          <label class="form-control">
            <div class="label"><span class="label-text">Turno</span></div>
            <select name="round_id" class="select select-bordered" required>
              @forelse($rounds as $r)
                <option value="{{ $r->id }}">
                  {{ $r->name ?? ('Turno '.$r->round_no) }}{{ $r->status === 'published' ? '' : ' (draft)' }}
                </option>
              @empty
                <option value="">Nessun turno trovato</option>
              @endforelse
            </select>
          </label>

          <label class="form-control">
            <div class="label"><span class="label-text">Tipo import</span></div>
            <select name="mode" class="select select-bordered" required>
              <option value="danese_two_rows">Danese (TAB: Girone, Tavolo, Coppia — 2 righe per tavolo: prima F poi M)</option>
              <option value="by_table">In ordine di tavolo (Tavolo; Coppia A; Coppia B)</option>
              <option value="alphabetical">In ordine alfabetico (Coppia; Tavolo)</option>
            </select>
          </label>
        </div>

        <label class="form-control">
          <div class="label"><span class="label-text">Nome sorgente (opzionale)</span></div>
          <input name="source_name" class="input input-bordered" placeholder="es. export_turno1_2026-01-07.txt">
        </label>

        <label class="form-control">
          <div class="label"><span class="label-text">Dati incollati</span></div>
          <textarea name="payload"
                    class="textarea textarea-bordered min-h-[240px]"
                    placeholder="Esempio (danese_two_rows) TAB separato:
A    1    ROSSI MARIO - BIANCHI MARTINA
A    1    VERDI FABIO - GIALLI MARCO
A    2    NERI GIACOMO - VIOLA IVANA

NOTE: eventuali colonne extra a destra verranno ignorate."
                    required></textarea>
          <div class="label">
            <span class="label-text-alt">Separatori supportati: TAB (consigliato), ; oppure ,</span>
          </div>
        </label>

        <div class="flex gap-2">
          <button class="btn btn-primary" @disabled($rounds->isEmpty())>Importa</button>
          <a class="btn btn-outline" href="{{ route('admin.import', ['event_id' => $eventId, 'key' => request('key')]) }}">Reset</a>
        </div>
      </form>

      @if(session('import_errors'))
        <div class="mt-4">
          <div class="font-semibold">Avvisi / errori rilevati</div>
          <ul class="list-disc pl-5 text-sm text-error">
            @foreach(session('import_errors') as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

    </div>
  </div>

</div>
@endsection
