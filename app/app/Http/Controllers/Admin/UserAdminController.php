<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SetupLinkMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\CarbonImmutable;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');   // invited|active|all
        $active = (string) $request->query('active', 'all');   // 1|0|all
        $trashed = (string) $request->query('trashed', 'all'); // 0|1|all

        $usersQuery = User::query();

        // Soft-delete filter
        if ($trashed === '1') {
            $usersQuery->onlyTrashed();
        } elseif ($trashed === 'all') {
            $usersQuery->withTrashed();
        } // '0' => default (solo non cancellati)

        // Search
        if ($q !== '') {
            $usersQuery->where(function ($qq) use ($q) {
                $qq->where('email', 'ilike', "%{$q}%")
                    ->orWhere('name', 'ilike', "%{$q}%")
                    ->orWhere('display_name', 'ilike', "%{$q}%");
            });
        }

        // Status filter
        if ($status !== 'all' && $status !== '') {
            $usersQuery->where('status', $status);
        }

        // Active filter
        if ($active !== 'all' && $active !== '') {
            $usersQuery->where('is_active', $active === '1');
        }

        $users = $usersQuery
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users'   => $users,
            'q'       => $q,
            'status'  => $status,
            'active'  => $active,
            'trashed' => $trashed,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.users.create');
    }

	public function edit(Request $request, User $user)
	{
		return view('admin.users.edit', [
			'user' => $user,
			'admin_key' => $request->query('admin_key') ?: $request->query('key'),
		]);
	}

	public function update(Request $request, User $user)
	{
		if ($user->trashed()) abort(403);

		$data = $request->validate([
			'email' => [
				'required','email','max:190',
				Rule::unique('users', 'email')
					->ignore($user->id)
					->whereNull('deleted_at'),
			],
			'name' => ['nullable','string','max:120'],
			'display_name' => ['nullable','string','max:120'],
			'admin_note' => ['nullable','string','max:2000'],
		]);

		$email = strtolower(trim($data['email']));
		$emailChanged = strtolower($user->email) !== $email;

		$user->fill([
			'email' => $email,
			'name' => $data['name'] ?? $user->name,
			'display_name' => $data['display_name'] ?? $user->display_name,
			'admin_note' => $data['admin_note'] ?? $user->admin_note,
		])->save();

		return redirect()
			->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
			->with('flash_success', $emailChanged ? 'Dati aggiornati (email modificata).' : 'Dati aggiornati.');
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'email'        => ['required', 'email', 'max:190', Rule::unique('users', 'email')->whereNull('deleted_at')],
			'display_name' => ['nullable', 'string', 'max:120'],
			'admin_note'   => ['nullable', 'string', 'max:2000'],
			'send_email'   => ['nullable'], // checkbox
		]);

		$email = strtolower(trim($data['email']));

		$plainSetupToken = 'su_' . Str::random(48);

		$user = new User([
			'email' => $email,
			'name' => ($data['display_name'] ?? null) ?: $email,
			'password' => Str::random(64),

			'display_name' => ($data['display_name'] ?? null) ?: null,
			'admin_note'   => ($data['admin_note'] ?? null) ?: null,

			'status' => User::STATUS_INVITED,
			'profile_completed' => false,

			'setup_token_hash' => Hash::make($plainSetupToken),
			'setup_expires_at' => now()->addDays(7),

			'owner_key_hash' => null,

			// invited non è operativo comunque; lo lasciamo true per UX admin
			'is_active' => true,
			'deactivated_reason' => null,

			'expired_at' => null,
			'inactive_expired_at' => null,

			'setup_completed_at' => null,
			'last_seen_at' => null,
		]);

		// ✅ Scadenza al 31/01 anno successivo alla CREAZIONE
		$user->recalculateExpiry(CarbonImmutable::now('Europe/Rome'));

		$user->save();

		$setupUrl = url('/setup/' . $plainSetupToken);

		$sent = false;
		if (($data['send_email'] ?? null) !== null) {
			Mail::to($user->email)->send(new SetupLinkMail($setupUrl));
			$sent = true;
		}

		return redirect()
			->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
			->with('flash_success', 'Utente creato.')
			->with('flash_setup_url', $setupUrl)
			->with('flash_user_email', $user->email)
			->with('flash_mail_sent', $sent);
	}


    public function regenerateSetupLink(Request $request, User $user)
    {
        if ($user->trashed()) {
            abort(403);
        }

        if ($user->status !== User::STATUS_INVITED) {
            return redirect()
                ->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
                ->with('flash_error', 'Setup link rigenerabile solo per utenti in stato invited.');
        }

        $plainSetupToken = 'su_' . Str::random(48);

        $user->update([
            'setup_token_hash' => Hash::make($plainSetupToken),
            'setup_expires_at' => now()->addDays(7),
        ]);

        $setupUrl = url('/setup/' . $plainSetupToken);

        // Nel tuo flusso: rigenero e invio sempre (ok)
        Mail::to($user->email)->send(new SetupLinkMail($setupUrl));

        return redirect()
            ->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
            ->with('flash_success', 'Setup link rigenerato e inviato.')
            ->with('flash_setup_url', $setupUrl)
            ->with('flash_user_email', $user->email)
            ->with('flash_mail_sent', true);
    }

	public function toggleActive(Request $request, User $user)
	{
		if ($user->trashed()) {
			abort(403);
		}

		// disattivo: non tocco expires_at
		if ($user->is_active) {
			$user->is_active = false;
			$user->deactivated_reason = 'manual';
			$user->save();

			return redirect()
				->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
				->with('flash_success', 'Utente disattivato.');
		}

		// riattivo: reset scadenza al 31/01 anno successivo
		$user->is_active = true;
		$user->deactivated_reason = null;
		$user->recalculateExpiry(\Carbon\CarbonImmutable::now('Europe/Rome'));
		$user->save();

		return redirect()
			->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
			->with('flash_success', 'Utente riattivato.');
	}

    // Soft-delete
    public function destroy(Request $request, User $user)
    {
        // opzionale: marca motivo prima del cestino (utile se poi fai report)
        $user->deactivated_reason = 'deleted';
        $user->is_active = false;
        $user->save();

        $user->delete();

        return redirect()
            ->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
            ->with('flash_success', 'Utente spostato nel cestino.');
    }

    // Restore (trashed)
	public function restore(Request $request, int $id)
	{
		$user = User::withTrashed()->findOrFail($id);
		$user->restore();

		$user->is_active = true;
		$user->deactivated_reason = null;
		$user->recalculateExpiry(\Carbon\CarbonImmutable::now('Europe/Rome'));
		$user->save();

		return redirect()
			->route('admin.users.index', ['admin_key' => $request->query('admin_key') ?: $request->query('key')])
			->with('flash_success', 'Utente ripristinato.');
	}
}