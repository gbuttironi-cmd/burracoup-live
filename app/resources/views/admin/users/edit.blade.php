@extends('layouts.app')

@section('content')
@php
  $adminKey = request('admin_key') ?: request('key');
  $isExpired = $user->expires_at && $user->expires_at->isPast();
  $operational = $user->isActiveUser();
@endphp

<div class="max-w-3xl mx-auto p-6 space-y-6">

  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold">Modifica utente</h1>
      <p class="opacity-70 mt-1">Correggi email e dati anagrafici. Le modifiche sono immediate.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.users.index', ['admin_key' => $adminKey]) }}">
      ← Torna alla lista
    </a>
  </div>

  {{-- Stato sintetico --}}
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <div class="flex flex-wrap items-center gap-2">
        <span class="badge badge-outline">onboarding: {{ $user->status }}</span>

        @if($user->trashed())
          <span class="badge">cestino</span>
        @elseif($operational)
          <span class="badge badge-success">operativo: attivo</span>
        @elseif($isExpired)
          <span class="badge badge-warning">operativo: scaduto</span>
        @elseif(!$user->is_active)
          <span class="badge badge-error">operativo: disattivato</span>
        @else
          <span class="badge">operativo: —</span>
        @endif
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-sm">
        <div>
          <div class="opacity-60">Scadenza</div>
          <div class="{{ $isExpired ? 'text-warning font-semibold' : '' }}">
            {{ $user->expires_at ? $user->expires_at->timezone('Europe/Rome')->format('d/m/Y') : '—' }}
          </div>
        </div>

        <div>
          <div class="opacity-60">Setup completato</div>
          <div>
            {{ $user->setup_completed_at ? $user->setup_completed_at->timezone('Europe/Rome')->format('d/m/Y H:i') : '—' }}
          </div>
        </div>

        <div>
          <div class="opacity-60">Ultimo accesso</div>
          <div>
            {{ $user->last_seen_at ? $user->last_seen_at->timezone('Europe/Rome')->format('d/m/Y H:i') : '—' }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Form --}}
  <div class="card bg-base-100 shadow">
    <div class="card-body">
      <h2 class="card-title">Dati modificabili</h2>

      <form method="POST" action="{{ route('admin.users.update', ['user' => $user->id, 'admin_key' => $adminKey]) }}" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          {{-- Email --}}
          <div>
            <label class="label">
              <span class="label-text font-medium">Email</span>
            </label>
            <input
              type="email"
              name="email"
              class="input input-bordered w-full @error('email') input-error @enderror"
              value="{{ old('email', $user->email) }}"
              required
              autocomplete="off"
            />
            @error('email')
              <p class="text-error text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs opacity-60 mt-1">
              Se l’utente è <span class="font-semibold">invited</span> e cambi l’email, valuta di rigenerare e reinviare il setup link.
            </p>
          </div>

          {{-- Display name --}}
          <div>
            <label class="label">
              <span class="label-text font-medium">Nome visualizzato</span>
            </label>
            <input
              type="text"
              name="display_name"
              class="input input-bordered w-full @error('display_name') input-error @enderror"
              value="{{ old('display_name', $user->display_name) }}"
              maxlength="120"
              autocomplete="off"
            />
            @error('display_name')
              <p class="text-error text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Name --}}
          <div>
            <label class="label">
              <span class="label-text font-medium">Nome (campo tecnico)</span>
            </label>
            <input
              type="text"
              name="name"
              class="input input-bordered w-full @error('name') input-error @enderror"
              value="{{ old('name', $user->name) }}"
              maxlength="120"
              autocomplete="off"
            />
            @error('name')
              <p class="text-error text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs opacity-60 mt-1">
              Di solito puoi lasciarlo allineato al nome visualizzato o all’email.
            </p>
          </div>

          {{-- Admin note --}}
          <div class="md:col-span-2">
            <label class="label">
              <span class="label-text font-medium">Nota admin</span>
            </label>
            <textarea
              name="admin_note"
              class="textarea textarea-bordered w-full min-h-[110px] @error('admin_note') textarea-error @enderror"
              maxlength="2000"
            >{{ old('admin_note', $user->admin_note) }}</textarea>
            @error('admin_note')
              <p class="text-error text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
          <div class="flex gap-2">
            <a class="btn btn-ghost" href="{{ route('admin.users.index', ['admin_key' => $adminKey]) }}">
              Annulla
            </a>

            <button class="btn btn-primary" type="submit">
              Salva modifiche
            </button>
          </div>

          {{-- Quick actions (solo informative, azioni vere stanno nella lista o in controller dedicati) --}}
          <div class="text-xs opacity-60">
            Utente ID interno: {{ $user->id }} (non mostrato in lista)
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Safety: se in cestino --}}
  @if($user->trashed())
    <div class="alert">
      <span>Questo utente è nel cestino. Ripristinalo dalla lista utenti prima di modificarlo operativamente.</span>
    </div>
  @endif

</div>
@endsection