<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupController extends Controller
{
    public function show(string $token)
    {
        $user = $this->findInvitedBySetupToken($token);

        if (!$user) {
            abort(404);
        }

        if ($this->isSetupExpired($user)) {
            return response()->view('setup.expired', [], 410);
        }

        $profile = $user->profile()->first();

        return view('setup.form', [
            'token' => $token,

            // email proposta: se profilo già parzialmente compilato, usa quella
            'email' => $profile->email_contatto ?? $user->email,

            // non stiamo modificando i dati admin, ma possiamo usarli come prefill
            'name'  => $user->name,
        ]);
    }

public function store(Request $request, string $token)
{
    $user = $this->findInvitedBySetupToken($token);

    if (!$user) {
        abort(404);
    }

    if ($this->isSetupExpired($user)) {
        return response()->view('setup.expired', [], 410);
    }

    $data = $request->validate([
        'email_contatto'    => ['required', 'email', 'max:190'],

        'presidente'        => ['required', 'string', 'max:120'],
        'telefono'          => ['required', 'string', 'max:40'],

        'nome_associazione' => ['required', 'string', 'max:190'],
        'regione'           => ['required', 'string', 'max:80'],
        'provincia'         => ['required', 'string', 'max:80'],
        'citta'             => ['required', 'string', 'max:120'],
        'sede_indirizzo'    => ['required', 'string', 'max:190'],

        'giorni_orari'            => ['required', 'array', 'min:1', 'max:14'],
        'giorni_orari.*.giorno'   => ['required', 'string', 'max:20'],
        'giorni_orari.*.da'       => ['required', 'date_format:H:i'],
        'giorni_orari.*.a'        => ['required', 'date_format:H:i'],
    ]);

    $ownerKeyPlain = null;

    DB::transaction(function () use ($user, $data, &$ownerKeyPlain) {

        // 1) Profilo associazione (create-or-update)
        /** @var \App\Models\UserProfile $profile */
        $profile = $user->profile()->first() ?? new UserProfile(['user_id' => $user->id]);

        // Nota: non tocchiamo i campi legacy (public_name, city, contact_email) per non fare danni.
        $profile->fill([
            'email_contatto'    => $data['email_contatto'],
            'presidente'        => $data['presidente'],
            'telefono'          => $data['telefono'],
            'nome_associazione' => $data['nome_associazione'],
            'regione'           => $data['regione'],
            'provincia'         => $data['provincia'],
            'citta'             => $data['citta'],
            'sede_indirizzo'    => $data['sede_indirizzo'],
            'giorni_orari'      => $data['giorni_orari'],
        ]);

        // valori base se non presenti (MVP-safe)
        if (!$profile->timezone) {
            $profile->timezone = 'Europe/Rome';
        }
        if (!$profile->type) {
            $profile->type = 'associazione';
        }
        if (!$profile->default_refresh_seconds) {
            $profile->default_refresh_seconds = 120;
        }

        $profile->save();

        // 2) Owner key: generata e salvata SOLO hash
        $ownerKeyPlain = 'ok_' . Str::random(48);
        $user->owner_key_hash = Hash::make($ownerKeyPlain);

        // 3) Attivazione + audit minimo
        $user->status = User::STATUS_ACTIVE;
        $user->profile_completed = true;
        $user->setup_completed_at = now();
        $user->last_seen_at = now();

        // operativo
        $user->is_active = true;
        $user->deactivated_reason = null;

        // scadenza al 31/01 anno successivo (logica già nel model)
        $user->recalculateExpiry(CarbonImmutable::now('Europe/Rome'));

        // 4) Token one-time: invalida
        $user->setup_token_hash = null;
        $user->setup_expires_at = null;

        // 5) Password tecnica (se vuota)
        if (empty($user->password)) {
            $user->password = Str::random(64);
        }

        $user->save();
    });

    session()->flash('owner_key_plain', $ownerKeyPlain);

    return redirect()->route('setup.done');
}

    private function isSetupExpired(User $user): bool
    {
        return $user->setup_expires_at !== null && $user->setup_expires_at->isPast();
    }

    /**
     * Trova l'utente invited associato al token (hash-only).
     * Ritorna null se token non corrisponde / utente non eligible.
     */
    private function findInvitedBySetupToken(string $token): ?User
    {
        if (!str_starts_with($token, 'su_') || strlen($token) < 10) {
            return null;
        }

        $candidates = User::query()
            ->where('status', User::STATUS_INVITED)
            ->whereNull('deleted_at')
            ->whereNotNull('setup_token_hash')
            ->get([
                'id','email','name','display_name','status',
                'setup_token_hash','setup_expires_at',
                'deleted_at','password','profile_completed','is_active'
            ]);

        foreach ($candidates as $u) {
            if (Hash::check($token, $u->setup_token_hash)) {
                return $u;
            }
        }

        return null;
    }
}