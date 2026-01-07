@extends('layouts.app')

@section('content')
@php
  $view = request('view', 'tables'); // tables|alpha

  $btnActive = 'btn btn-primary';
  $btnIdle   = 'btn btn-outline';
@endphp

<div class="space-y-6">

  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
          <p class="text-base-content/70">
            {{ $event->association_name }} · {{ ucfirst($event->type) }}
            @if($event->city || $event->region)
              · {{ $event->city ?? '' }}{{ $event->region ? ', '.$event->region : '' }}
            @endif
          </p>
          @if($event->start_at)
            <p class="text-sm mt-1">
              <span class="font-semibold">Inizio:</span> {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
            </p>
          @endif
        </div>

        <div class="flex gap-2">
          <a href="{{ route('events.index') }}" class="btn btn-outline">← Eventi</a>
        </div>
      </div>

      {{-- Toggle principale: Tavoli / Classifica (btn) + selezione turno --}}
      <div class="mt-4 flex flex-col lg:flex-row lg:items-end gap-3">
        <div class="flex flex-wrap gap-2">
          <a class="{{ $tab === 'tables' ? $btnActive : $btnIdle }}"
             href="{{ route('events.show', $event->id) }}?tab=tables{{ $activeRoundId ? '&round_id='.$activeRoundId : '' }}&view={{ $view }}">
            Tavoli
          </a>

          <a class="{{ $tab === 'standings' ? $btnActive : $btnIdle }}"
             href="{{ route('events.show', $event->id) }}?tab=standings{{ $activeRoundId ? '&round_id='.$activeRoundId : '' }}">
            Classifica
          </a>
        </div>

        <div class="lg:ml-auto">
          <form method="GET" class="flex gap-2 items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if($tab === 'tables')
              <input type="hidden" name="view" value="{{ $view }}">
            @endif

            <label class="form-control">
              <div class="label"><span class="label-text">Turno</span></div>
              <select name="round_id" class="select select-bordered" onchange="this.form.submit()">
                @foreach($rounds as $r)
                  <option value="{{ $r->id }}" @selected((int)$activeRoundId === (int)$r->id)>
                    {{ $r->name ?? ('Turno '.$r->round_no) }}{{ $r->status === 'published' ? '' : ' (draft)' }}
                  </option>
                @endforeach
              </select>
            </label>
          </form>
        </div>
      </div>

      {{-- Toggle secondario: Per tavolo / Alfabetico (solo tab=tables) --}}
      @if($tab === 'tables')
        <div class="mt-4 flex flex-wrap gap-2">
          <a class="{{ $view === 'tables' ? $btnActive : $btnIdle }}"
             href="{{ route('events.show', $event->id) }}?tab=tables&round_id={{ $activeRoundId }}&view=tables">
            Per tavolo
          </a>

          <a class="{{ $view === 'alpha' ? $btnActive : $btnIdle }}"
             href="{{ route('events.show', $event->id) }}?tab=tables&round_id={{ $activeRoundId }}&view=alpha">
            Alfabetico
          </a>
        </div>
      @endif

    </div>
  </div>

  {{-- CONTENUTO --}}
  @if($tab === 'tables')
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <h2 class="card-title">Posizione ai tavoli</h2>

        @if($tables->isEmpty())
          <div class="alert">
            <span>Nessun tavolo importato per questo turno.</span>
          </div>
        @else

          {{-- Vista: Per tavolo --}}
          @if($view === 'tables')
            <div class="overflow-x-auto mt-3">
              <table class="table table-zebra-force text-base">
                <thead>
                  <tr>
                    <th class="w-24">Tavolo</th>
                    <th>Fisso</th>
                    <th>Mobile</th>
                  </tr>
                </thead>
				<tbody>
				  @foreach($tables as $t)
					<tr style="background-color: {{ $loop->odd ? 'rgba(124,58,237,0.06)' : 'transparent' }};">
					  <td class="font-bold">{{ $t->table_no }}</td>
					  <td>{{ $t->fixed_pair_name ?? '-' }}</td>
					  <td>{{ $t->mobile_pair_name ?? '-' }}</td>
					</tr>
				  @endforeach
				</tbody>

              </table>
            </div>

          {{-- Vista: Alfabetico --}}
          @else
            <div class="overflow-x-auto mt-3">
              <table class="table table-zebra-force text-base">
                <thead>
                  <tr>
                    <th>Coppia</th>
                    <th class="w-40">Fisso/Mobile</th>
                    <th class="w-24">Tavolo</th>
                  </tr>
                </thead>
				<tbody>
				  @foreach($alphaRows as $r)
					<tr style="background-color: {{ $loop->odd ? 'rgba(124,58,237,0.06)' : 'transparent' }};">
					  <td class="font-semibold">{{ $r->pair_name }}</td>
					  <td><span class="badge badge-primary text-primary-content">{{ $r->label }}</span></td>
					  <td class="font-bold">{{ $r->table_no }}</td>
					</tr>
				  @endforeach
				</tbody>

              </table>
            </div>
          @endif

        @endif
      </div>
    </div>

  @else
    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <h2 class="card-title">Classifica</h2>

        @if($standings->isEmpty())
          <div class="alert">
            <span>Nessuna classifica importata.</span>
          </div>
        @else
          <div class="overflow-x-auto mt-3">
            <table class="table table-zebra-force text-base">
              <thead>
                <tr>
                  <th class="w-20">#</th>
                  <th>Competitore</th>
                  <th class="w-28">Punti</th>
                </tr>
              </thead>
			<tbody>
			  @foreach($standings as $s)
				<tr style="background-color: {{ $loop->odd ? 'rgba(124,58,237,0.06)' : 'transparent' }};">
				  <td class="font-bold">{{ $s->rank ?? '-' }}</td>
				  <td class="font-semibold">{{ $s->competitor_name }}</td>
				  <td class="font-bold">{{ $s->points ?? '-' }}</td>
				</tr>
			  @endforeach
			</tbody>

            </table>
          </div>
        @endif
      </div>
    </div>
  @endif

</div>
@endsection
