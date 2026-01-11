@extends('layouts.app')

@section('content')
  <div class="space-y-6">

    <div class="card bg-base-100 shadow-lg">
      <div class="card-body">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">BurracoUP LIVE</h1>
            <p class="mt-1 text-base-content/70">
              Eventi, tavoli e classifiche in tempo reale. Zero caos, info chiare.
            </p>
          </div>

          <div class="flex gap-2">
            <a href="/eventi" class="btn btn-primary text-white">Cerca eventi</a>
            <a href="/giocatori" class="btn btn-outline">Trova giocatore</a>
          </div>
        </div>
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <div class="text-sm font-semibold opacity-70">Prima dell’evento</div>
          <div class="text-lg font-bold">Info torneo</div>
          <p class="text-base-content/70 text-sm">
            Orari, convenzioni, mappa, iscritti e tavoli (alfabetico).
          </p>
        </div>
      </div>

      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <div class="text-sm font-semibold opacity-70">Durante</div>
          <div class="text-lg font-bold">Tavoli & Danese</div>
          <p class="text-base-content/70 text-sm">
            Turno corrente, fisso/mobile, abbinamenti e avversari.
          </p>
        </div>
      </div>

      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <div class="text-sm font-semibold opacity-70">Live</div>
          <div class="text-lg font-bold">Classifiche</div>
          <p class="text-base-content/70 text-sm">
            Per turno, temporanea e finale + premi e gironi.
          </p>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 shadow">
      <div class="card-body">
        <div class="flex items-center justify-between">
          <div>
            <div class="font-bold">Suggerimento</div>
            <div class="text-sm text-base-content/70">
              Salva l’evento tra i preferiti: lo ritrovi subito quando arrivi al torneo.
            </div>
          </div>
          <a href="/eventi" class="btn btn-ghost">Vai agli eventi →</a>
        </div>
      </div>
    </div>

  </div>
@endsection

