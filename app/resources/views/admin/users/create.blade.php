@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6 space-y-6">

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Nuovo utente</h1>
    <a class="btn btn-ghost"
       href="{{ route('admin.users.index', ['key' => request('key') ?? request('admin_key')]) }}">
      ← Torna
    </a>
  </div>

  @if($errors->any())
    <div class="alert alert-error">
      <ul class="list-disc pl-6">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ route('admin.users.store', ['key' => request('key') ?? request('admin_key')]) }}"
        class="card bg-base-100 shadow">
    @csrf
    <div class="card-body space-y-4">

      <div>
        <label class="label"><span class="label-text">Email *</span></label>
        <input name="email" type="email" class="input input-bordered w-full" required value="{{ old('email') }}">
      </div>

      <div>
        <label class="label"><span class="label-text">Nome visualizzato</span></label>
        <input name="display_name" class="input input-bordered w-full" value="{{ old('display_name') }}" placeholder="es. Associazione Burraco XYZ">
      </div>

      <div>
        <label class="label"><span class="label-text">Nota admin</span></label>
        <textarea name="admin_note" class="textarea textarea-bordered w-full" rows="3">{{ old('admin_note') }}</textarea>
      </div>

      <div class="form-control">
        <label class="label cursor-pointer justify-start gap-3">
          <input type="checkbox" class="checkbox" name="send_email" checked>
          <span class="label-text">Invia subito email con setup link</span>
        </label>
      </div>

      <div class="flex gap-2 justify-end">
        <button class="btn btn-primary">Crea</button>
      </div>

    </div>
  </form>

</div>
@endsection