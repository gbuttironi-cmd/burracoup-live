@extends('layouts.app')

@section('content')
@php
  $adminKey = request('key') ?? request('admin_key');
  $trashed = (string) request('trashed', 'all'); // all | 0 | 1

  // Mantieni filtri quando cambi “Attivi/Cancellati/Tutti”
  $base = [
    (request('admin_key') ? 'admin_key' : 'key') => $adminKey,
    'q' => request('q'),
    'status' => request('status', 'all'),
    'active' => request('active', 'all'),
  ];
@endphp

<div class="max-w-6xl mx-auto p-6 space-y-6">

  <div class="flex items-center justify-between gap-3">
    <h1 class="text-2xl font-semibold">Utenti</h1>

    <a class="btn btn-primary"
       href="{{ route('admin.users.create', [(request('admin_key')?'admin_key':'key') => $adminKey]) }}">
      + Nuovo utente
    </a>
  </div>

  {{-- Flash --}}
  @if(session('flash_error'))
    <div class="alert alert-error">{{ session('flash_error') }}</div>
  @endif
  @if(session('flash_success'))
    <div class="alert alert-success">{{ session('flash_success') }}</div>
  @endif

  @if(session('flash_setup_url'))
    <div class="alert alert-info">
      <div class="w-full space-y-2">
        <div class="font-semibold">
          Setup link per {{ session('flash_user_email') }}
          @if(session('flash_mail_sent'))
            <span class="badge badge-success ml-2">email inviata</span>
          @else
            <span class="badge badge-outline ml-2">email non inviata</span>
          @endif
        </div>

        <div class="join w-full">
          <input id="setupLink"
                 class="input input-bordered join-item w-full font-mono text-sm"
                 readonly
                 value="{{ session('flash_setup_url') }}">
          <button class="btn join-item"
                  type="button"
                  onclick="navigator.clipboard.writeText(document.getElementById('setupLink').value)">
            Copia
          </button>
        </div>

        <div class="text-xs opacity-60">
          Se l’email non arriva, puoi passare manualmente questo link.
        </div>
      </div>
    </div>
  @endif

  {{-- Filtri ricerca/status/attivo --}}
<form method="GET" class="card bg-base-100 shadow">
  <div class="card-body gap-4">

    {{-- admin key --}}
    <input type="hidden" name="{{ request('admin_key') ? 'admin_key' : 'key' }}" value="{{ $adminKey }}">
    {{-- trashed --}}
    <input type="hidden" name="trashed" value="{{ $trashed }}">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

      {{-- Ricerca --}}
      <div class="md:col-span-6">
        <label class="label">
          <span class="label-text">Cerca</span>
        </label>
        <input
          class="input input-bordered w-full"
          name="q"
          value="{{ $q ?? '' }}"
          placeholder="email / nome"
        >
      </div>

      {{-- Status --}}
      <div class="md:col-span-2">
        <label class="label">
          <span class="label-text">Status</span>
        </label>
        <select class="select select-bordered w-full" name="status">
          <option value="all" @selected(($status ?? 'all')==='all')>Tutti</option>
          <option value="invited" @selected(($status ?? '')==='invited')>invited</option>
          <option value="active" @selected(($status ?? '')==='active')>active</option>
        </select>
      </div>

      {{-- Attivo --}}
      <div class="md:col-span-2">
        <label class="label">
          <span class="label-text">Operativo</span>
        </label>
        <select class="select select-bordered w-full" name="active">
          <option value="all" @selected(($active ?? 'all')==='all')>Tutti</option>
          <option value="1" @selected(($active ?? '')==='1')>Attivo</option>
          <option value="0" @selected(($active ?? '')==='0')>Disattivato</option>
        </select>
      </div>

      {{-- Azioni --}}
		<div class="md:col-span-2 flex flex-col md:flex-row gap-2">
		  <button class="btn btn-primary w-full md:w-auto">
			Filtra
		  </button>
		  </div>
		<div class="md:col-span-2 flex flex-col md:flex-row gap-2">
		  <button
			type="button"
			class="btn btn-ghost w-full md:w-auto"
			onclick="window.location.href='{{ route('admin.users.index', [(request('admin_key')?'admin_key':'key') => $adminKey, 'trashed' => $trashed]) }}'"
		  >
			Reset filtri
		  </button>
		</div>


    </div>
  </div>
</form>

  {{-- Filtri soft-delete: Attivi / Cancellati / Tutti --}}
  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div class="join">
      <a class="btn join-item {{ ($trashed === '0') ? 'btn-primary' : 'btn-outline' }}"
         href="{{ route('admin.users.index', array_merge($base, ['trashed' => '0'])) }}">
        Attivi
      </a>

      <a class="btn join-item {{ ($trashed === '1') ? 'btn-primary' : 'btn-outline' }}"
         href="{{ route('admin.users.index', array_merge($base, ['trashed' => '1'])) }}">
        Cancellati
      </a>

      <a class="btn join-item {{ ($trashed === 'all' || $trashed === '') ? 'btn-primary' : 'btn-outline' }}"
         href="{{ route('admin.users.index', array_merge($base, ['trashed' => 'all'])) }}">
        Tutti
      </a>
    </div>

    <div class="text-sm opacity-70">
      @if($trashed === '0')
        Mostrando: attivi
      @elseif($trashed === '1')
        Mostrando: cancellati
      @else
        Mostrando: tutti
      @endif
    </div>
  </div>

  {{-- Tabella --}}
<div class="card bg-base-100 shadow">
  <div class="card-body p-0">
    <div class="overflow-x-auto">
      <table class="table table-zebra w-full min-w-[860px] md:min-w-0">
        <thead>
          <tr>
            <th>Email</th>
            <th>Nome</th>

            {{-- nascondo su mobile, le metto nella riga "dettagli" --}}
            <th class="hidden md:table-cell">Onboarding</th>
            <th class="hidden md:table-cell">Operativo</th>
            <th class="hidden md:table-cell">Scadenza</th>

            <th class="hidden lg:table-cell">Setup</th>
            <th class="hidden lg:table-cell">Ultimo accesso</th>
            <th class="text-right">Azioni</th>
          </tr>
        </thead>

        <tbody>
          @foreach($users as $u)
            @php
              $isExpired = $u->expires_at && $u->expires_at->isPast();
              $operational = $u->isActiveUser();
              $name = $u->display_name ?: $u->name ?: '—';
            @endphp

            <tr class="{{ $u->trashed() ? 'opacity-50' : '' }}">
              <td class="whitespace-nowrap">
                <div class="font-medium">{{ $u->email }}</div>
              </td>

              <td class="whitespace-nowrap">
                <div>{{ $name }}</div>
              </td>

              <td class="hidden md:table-cell whitespace-nowrap">
                @if($u->status === 'invited')
                  <span class="badge badge-ghost">invited</span>
                @else
                  <span class="badge badge-outline">active</span>
                @endif
              </td>

              <td class="hidden md:table-cell whitespace-nowrap">
                @if($u->trashed())
                  <span class="badge">cestino</span>
                @elseif($operational)
                  <span class="badge badge-success">attivo</span>
                @elseif($isExpired)
                  <span class="badge badge-warning">scaduto</span>
                @elseif(!$u->is_active)
                  <span class="badge badge-error">disattivato</span>
                @else
                  <span class="badge">—</span>
                @endif
              </td>

              <td class="hidden md:table-cell whitespace-nowrap">
                @if($u->expires_at)
                  <span class="{{ $isExpired ? 'text-warning font-semibold' : '' }}">
                    {{ $u->expires_at->timezone('Europe/Rome')->format('d/m/Y') }}
                  </span>
                @else
                  —
                @endif
              </td>

              <td class="hidden lg:table-cell whitespace-nowrap">
                {{ $u->setup_completed_at ? $u->setup_completed_at->format('d/m/Y H:i') : '—' }}
              </td>

              <td class="hidden lg:table-cell whitespace-nowrap">
                {{ $u->last_seen_at ? $u->last_seen_at->format('d/m/Y H:i') : '—' }}
              </td>

              <td class="text-right whitespace-nowrap">
                <details class="dropdown dropdown-end">
                  <summary class="btn btn-sm m-1">⋯</summary>

                  <ul class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-56">
                    <li>
                      <a href="{{ route('admin.users.edit', ['user' => $u->id, (request('admin_key') ? 'admin_key' : 'key') => (request('admin_key') ?: request('key'))]) }}">
                        Modifica
                      </a>
                    </li>

                    @if(!$u->trashed())
                      <li>
                        <form method="POST" action="{{ route('admin.users.toggle', ['user' => $u->id, (request('admin_key') ? 'admin_key' : 'key') => (request('admin_key') ?: request('key'))]) }}">
                          @csrf
                          <button type="submit" class="w-full text-left">
                            {{ $u->is_active ? 'Disattiva' : 'Riattiva' }}
                          </button>
                        </form>
                      </li>

                      @if($u->status === 'invited')
                        <li>
                          <form method="POST" action="{{ route('admin.users.regenerate', ['user' => $u->id, (request('admin_key') ? 'admin_key' : 'key') => (request('admin_key') ?: request('key'))]) }}">
                            @csrf
                            <button type="submit" class="w-full text-left">Rigenera setup link</button>
                          </form>
                        </li>
                      @endif

                      <li class="text-error">
                        <form method="POST" action="{{ route('admin.users.destroy', ['user' => $u->id, (request('admin_key') ? 'admin_key' : 'key') => (request('admin_key') ?: request('key'))]) }}">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="w-full text-left">Cestina</button>
                        </form>
                      </li>
                    @else
                      <li>
                        <form method="POST" action="{{ route('admin.users.restore', ['id' => $u->id, (request('admin_key') ? 'admin_key' : 'key') => (request('admin_key') ?: request('key'))]) }}">
                          @csrf
                          <button type="submit" class="w-full text-left">Ripristina</button>
                        </form>
                      </li>
                    @endif
                  </ul>
                </details>
              </td>
            </tr>

            {{-- RIGA DETTAGLI SOLO MOBILE --}}
            <tr class="md:hidden {{ $u->trashed() ? 'opacity-50' : '' }}">
              <td colspan="3">
                <div class="flex flex-wrap gap-2 text-sm">
                  {{-- Onboarding --}}
                  @if($u->status === 'invited')
                    <span class="badge badge-ghost">Onboarding: invited</span>
                  @else
                    <span class="badge badge-outline">Onboarding: active</span>
                  @endif

                  {{-- Operativo --}}
                  @if($u->trashed())
                    <span class="badge">Operativo: cestino</span>
                  @elseif($operational)
                    <span class="badge badge-success">Operativo: attivo</span>
                  @elseif($isExpired)
                    <span class="badge badge-warning">Operativo: scaduto</span>
                  @elseif(!$u->is_active)
                    <span class="badge badge-error">Operativo: disattivato</span>
                  @else
                    <span class="badge">Operativo: —</span>
                  @endif

                  {{-- Scadenza --}}
                  @if($u->expires_at)
                    <span class="badge badge-outline">
                      Scadenza: {{ $u->expires_at->timezone('Europe/Rome')->format('d/m/Y') }}
                    </span>
                  @else
                    <span class="badge badge-outline">Scadenza: —</span>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>


    <div class="p-4">
      {{ $users->links() }}
    </div>
  </div>

</div>
@endsection