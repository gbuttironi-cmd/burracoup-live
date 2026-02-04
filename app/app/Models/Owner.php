<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $table = 'owners';

    protected $fillable = [
        'name',
        'type',
        'slug',
        'contact_email',
        'timezone',
        'default_refresh_seconds',
        'branding_json',
        'owner_key_hash',
        'is_active',
    ];

    protected $casts = [
        'branding_json' => 'array',
        'default_refresh_seconds' => 'integer',
        'is_active' => 'boolean',
    ];
}