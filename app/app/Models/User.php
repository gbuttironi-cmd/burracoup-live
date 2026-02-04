<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const STATUS_INVITED = 'invited';
    public const STATUS_ACTIVE  = 'active';

    protected $fillable = [
        // standard
        'name',
        'email',
        'password',

        // admin/user flow
        'status',
        'profile_completed',
        'setup_token_hash',
        'setup_expires_at',
        'owner_key_hash',
        'display_name',
        'admin_note',
        'is_active',

        // scadenze + audit leggero
        'expires_at',
        'expired_at',
        'inactive_expired_at',
        'deactivated_reason',
        'setup_completed_at',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'setup_token_hash',
        'owner_key_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',

            'setup_expires_at'    => 'datetime',
            'setup_completed_at'  => 'datetime',
            'last_seen_at'        => 'datetime',

            'profile_completed'   => 'boolean',
            'is_active'           => 'boolean',

            'expires_at'          => 'datetime',
            'expired_at'          => 'datetime',
            'inactive_expired_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function isInvited(): bool
    {
        return $this->status === self::STATUS_INVITED;
    }

    /**
     * Utente operativo = può usare il sistema.
     * Include: status, profilo, attivo, non cancellato, non scaduto.
     */
    public function isActiveUser(): bool
    {
        if ($this->trashed()) {
            return false;
        }

        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if (!$this->profile_completed) {
            return false;
        }

        if (!$this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Scadenza: 31/01 dell'anno successivo rispetto alla reference date.
     * La salviamo come START OF DAY per evitare slittamenti (01/02) dovuti a timezone/endOfDay.
     */
    public function recalculateExpiry(?CarbonImmutable $reference = null): void
    {
        $reference ??= CarbonImmutable::now('Europe/Rome');

        $this->expires_at = $reference
            ->addYear()
            ->setMonth(1)
            ->setDay(31)
            ->startOfDay();
    }

    /**
     * Disattiva l'utente e registra motivo + timestamp dedicato.
     * Motivi previsti: expired | inactive | manual | deleted
     */
    public function deactivate(string $reason): void
    {
        $this->is_active = false;
        $this->deactivated_reason = $reason;

        if ($reason === 'expired') {
            $this->expired_at = now();
        } elseif ($reason === 'inactive') {
            $this->inactive_expired_at = now();
        }
    }

    /**
     * Riattiva l'utente, pulisce i flag e ricalcola la scadenza.
     */
    public function activateAndResetExpiry(?CarbonImmutable $reference = null): void
    {
        $this->is_active = true;
        $this->deactivated_reason = null;

        $this->expired_at = null;
        $this->inactive_expired_at = null;

        $this->recalculateExpiry($reference ?? CarbonImmutable::now('Europe/Rome'));
    }

    /**
     * Baseline usata per inattività:
     * last_seen_at > setup_completed_at > created_at
     */
    public function baselineLastSeen()
    {
        return $this->last_seen_at
            ?? $this->setup_completed_at
            ?? $this->created_at;
    }

    /**
     * Scope: utenti operativi (per query pulite lato app).
     */
    public function scopeOperational(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ACTIVE)
            ->where('profile_completed', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where(function (Builder $qq) {
                $qq->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }
}