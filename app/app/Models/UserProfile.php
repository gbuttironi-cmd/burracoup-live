<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',

        // legacy / generici
        'public_name',
        'type',
        'city',
        'contact_email',
        'timezone',
        'default_refresh_seconds',
        'branding_json',

        // associazione (nuovi campi usati nel setup)
        'presidente',
        'telefono',
        'email_contatto',
        'nome_associazione',
        'regione',
        'provincia',
        'citta',
        'sede_indirizzo',
        'giorni_orari',
    ];

    protected $casts = [
        'branding_json' => 'array',
        'giorni_orari' => 'array',
        'default_refresh_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}