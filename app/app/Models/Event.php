<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'info_pre_evento' => 'array', // se poi lo riporti a text lo cambiamo
    ];
}
