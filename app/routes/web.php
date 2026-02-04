<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminImportController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\SetupController;

Route::get('/', function () {
    return redirect()->route('events.index');
});

Route::get('/eventi', [EventController::class, 'index'])->name('events.index');
Route::get('/eventi/{event}', [EventController::class, 'show'])->name('events.show');

// Admin area (protetta dal middleware admin.key)
Route::middleware('admin.key')->prefix('admin')->group(function () {

    // /admin  -> redirect alla lista utenti mantenendo key/admin_key
    Route::get('/', function () {
        $adminKey = request('admin_key') ?: request('key');

        // se non c'è chiave, il middleware dovrebbe già bloccare,
        // ma evitiamo redirect strani
        if (!$adminKey) {
            abort(403);
        }

        return redirect()->route('admin.users.index', [
            (request('admin_key') ? 'admin_key' : 'key') => $adminKey,
        ]);
    })->name('admin.home');

    // Import
    Route::get('/import', [AdminImportController::class, 'index'])->name('admin.import');
    Route::post('/import/tables', [AdminImportController::class, 'importTables'])->name('admin.import.tables');

    // Users
    Route::get('/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserAdminController::class, 'store'])->name('admin.users.store');

    Route::post('/users/{user}/regenerate-setup', [UserAdminController::class, 'regenerateSetupLink'])
        ->name('admin.users.regenerate');

    // Attiva / disattiva utente
    Route::post('/users/{user}/toggle-active', [UserAdminController::class, 'toggleActive'])
        ->name('admin.users.toggle');

    // Edit / update
    Route::get('/users/{user}/edit', [UserAdminController::class, 'edit'])
        ->name('admin.users.edit');

    Route::post('/users/{user}/update', [UserAdminController::class, 'update'])
        ->name('admin.users.update');

    // Elimina (soft-delete)
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])
        ->name('admin.users.destroy');

    // Restore (soft-delete)
    Route::post('/users/{id}/restore', [UserAdminController::class, 'restore'])
        ->name('admin.users.restore');
});

// Setup pubblico
Route::get('/setup/{token}', [SetupController::class, 'show'])->name('setup.show');
Route::post('/setup/{token}', [SetupController::class, 'store'])->name('setup.store');

Route::get('/setup-done', function () {
    $ownerKey = session('owner_key_plain');
    if (!$ownerKey) {
        abort(404);
    }
    return view('setup.done', ['ownerKey' => $ownerKey]);
})->name('setup.done');