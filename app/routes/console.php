<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ExpireUsersJob;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Qui definiamo comandi e scheduler.
| Questo file è caricato automaticamente da Laravel.
|
*/

// comando di esempio (puoi lasciarlo)
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');


// =======================================================
// Scheduler BurracoUP – Scadenza utenti
// =======================================================

Schedule::job(new ExpireUsersJob())
    ->dailyAt('02:00')
    ->timezone('Europe/Rome')
    ->withoutOverlapping()
    ->name('expire-users');
