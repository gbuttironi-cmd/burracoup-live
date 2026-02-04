@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto p-6">
    <h1 class="text-2xl font-bold mb-2">Profilo completato</h1>

    <div class="alert alert-warning mb-4">
        <span><strong>Questa chiave viene mostrata una sola volta.</strong> Copiala e conservala.</span>
    </div>

    <div class="p-4 rounded-lg bg-base-200 break-all font-mono text-sm">
        {{ $ownerKey }}
    </div>

    <p class="mt-4 opacity-80">Puoi chiudere questa pagina.</p>
</div>
@endsection