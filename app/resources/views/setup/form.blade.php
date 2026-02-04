@extends('layouts.app')

@section('content')
@php
  // $token, $email, $name arrivano dal SetupController@show
@endphp

<div class="max-w-2xl mx-auto p-6 space-y-6">
  <div>
    <h1 class="text-2xl font-bold">Completa registrazione associazione</h1>
    <p class="opacity-70 mt-1">
      Compila i dati mancanti per attivare l’utenza. L’email “account” inserita dall’admin non viene modificata.
    </p>
  </div>

  @if ($errors->any())
    <div class="alert alert-error">
      <div>
        <div class="font-semibold">Controlla i campi</div>
        <ul class="list-disc ml-5 mt-2">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('setup.store', ['token' => $token]) }}" class="card bg-base-100 shadow">
    @csrf
    <div class="card-body gap-4">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Email contatto (editabile, prefill da admin) --}}
        <div class="form-control md:col-span-2">
          <label class="label">
            <span class="label-text font-medium">Email contatto *</span>
          </label>

          <input
            type="email"
            name="email_contatto"
            class="input input-bordered w-full @error('email_contatto') input-error @enderror"
            required
            maxlength="190"
            autocomplete="email"
            value="{{ old('email_contatto', $email ?? '') }}"
            placeholder="nome@dominio.it"
          >

          @error('email_contatto')
            <div class="text-error text-sm mt-1">{{ $message }}</div>
          @enderror

          <div class="text-xs opacity-60 mt-1">
            Precompilata con l’email inserita dall’amministratore. Puoi correggerla qui: è l’email di contatto dell’associazione.
          </div>
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Presidente *</span></label>
          <input
            name="presidente"
            class="input input-bordered w-full @error('presidente') input-error @enderror"
            required
            maxlength="120"
            value="{{ old('presidente') }}"
            placeholder="Nome e Cognome"
            autocomplete="name"
          >
          @error('presidente') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Telefono *</span></label>
          <input
            name="telefono"
            class="input input-bordered w-full @error('telefono') input-error @enderror"
            required
            maxlength="40"
            value="{{ old('telefono') }}"
            placeholder="+39 ..."
            autocomplete="tel"
          >
          @error('telefono') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-control md:col-span-2">
          <label class="label"><span class="label-text font-medium">Nome associazione *</span></label>
          <input
            name="nome_associazione"
            class="input input-bordered w-full @error('nome_associazione') input-error @enderror"
            required
            maxlength="190"
            value="{{ old('nome_associazione') }}"
            placeholder="Es. Nuovo Burraco Bergamo"
          >
          @error('nome_associazione') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Regione *</span></label>
          <input
            name="regione"
            class="input input-bordered w-full @error('regione') input-error @enderror"
            required
            maxlength="80"
            value="{{ old('regione') }}"
            placeholder="Lombardia"
          >
          @error('regione') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Provincia *</span></label>
          <input
            name="provincia"
            class="input input-bordered w-full @error('provincia') input-error @enderror"
            required
            maxlength="80"
            value="{{ old('provincia') }}"
            placeholder="BG"
          >
          @error('provincia') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Città *</span></label>
          <input
            name="citta"
            class="input input-bordered w-full @error('citta') input-error @enderror"
            required
            maxlength="120"
            value="{{ old('citta') }}"
            placeholder="Bergamo"
          >
          @error('citta') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-control md:col-span-2">
          <label class="label"><span class="label-text font-medium">Sede di gioco – indirizzo *</span></label>
          <input
            name="sede_indirizzo"
            class="input input-bordered w-full @error('sede_indirizzo') input-error @enderror"
            required
            maxlength="190"
            value="{{ old('sede_indirizzo') }}"
            placeholder="Via / Piazza, numero civico"
          >
          @error('sede_indirizzo') <div class="text-error text-sm mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Giorni e orari --}}
      <div class="mt-2">
        <div class="font-medium mb-2">Giorni e orari di gioco *</div>

        @php
          $oldSlots = old('giorni_orari');
          $slots = is_array($oldSlots) ? $oldSlots : [ ['giorno'=>'Lunedì', 'da'=>'', 'a'=>''] ];
        @endphp

        <div id="slots" class="space-y-3">
          @foreach($slots as $i => $slot)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
              <div class="form-control md:col-span-2">
                <label class="label"><span class="label-text">Giorno</span></label>
                <select name="giorni_orari[{{ $i }}][giorno]" class="select select-bordered w-full">
                  @foreach(['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'] as $g)
                    <option value="{{ $g }}" @selected(($slot['giorno'] ?? '') === $g)>{{ $g }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-control">
                <label class="label"><span class="label-text">Da</span></label>
                <input
                  type="time"
                  name="giorni_orari[{{ $i }}][da]"
                  class="input input-bordered w-full"
                  value="{{ $slot['da'] ?? '' }}"
                  required
                >
              </div>

              <div class="form-control">
                <label class="label"><span class="label-text">A</span></label>
                <input
                  type="time"
                  name="giorni_orari[{{ $i }}][a]"
                  class="input input-bordered w-full"
                  value="{{ $slot['a'] ?? '' }}"
                  required
                >
              </div>
            </div>
          @endforeach
        </div>

        @error('giorni_orari')
          <div class="text-error text-sm mt-2">{{ $message }}</div>
        @enderror

        <button type="button" class="btn btn-outline btn-sm mt-3" onclick="addSlot()">
          + Aggiungi giorno/orario
        </button>

        <div class="text-xs opacity-60 mt-2">
          Inserisci una o più opzioni (es. Martedì 21:00–24:00).
        </div>
      </div>

      <div class="pt-2">
        <button class="btn btn-primary w-full" type="submit">
          Conferma e attiva
        </button>

        <div class="text-xs opacity-60 text-center mt-3">
          Il link è valido per 7 giorni. Se è scaduto, chiedi all’amministratore di rigenerarlo.
        </div>
      </div>

    </div>
  </form>
</div>

<script>
  function addSlot() {
    const container = document.getElementById('slots');
    const index = container.children.length;

    const html = `
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="form-control md:col-span-2">
          <label class="label"><span class="label-text">Giorno</span></label>
          <select name="giorni_orari[${index}][giorno]" class="select select-bordered w-full">
            <option>Lunedì</option><option>Martedì</option><option>Mercoledì</option>
            <option>Giovedì</option><option>Venerdì</option><option>Sabato</option><option>Domenica</option>
          </select>
        </div>
        <div class="form-control">
          <label class="label"><span class="label-text">Da</span></label>
          <input type="time" name="giorni_orari[${index}][da]" class="input input-bordered w-full" required>
        </div>
        <div class="form-control">
          <label class="label"><span class="label-text">A</span></label>
          <input type="time" name="giorni_orari[${index}][a]" class="input input-bordered w-full" required>
        </div>
      </div>
    `;
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    container.appendChild(wrapper.firstChild);
  }
</script>
@endsection