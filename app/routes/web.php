<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminImportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/eventi', [EventController::class, 'index'])->name('events.index');
Route::get('/eventi/{event}', [EventController::class, 'show'])->name('events.show');

Route::middleware('admin.key')->prefix('admin')->group(function () {
    Route::get('/import', [AdminImportController::class, 'index'])->name('admin.import');
    Route::post('/import/tables', [AdminImportController::class, 'importTables'])->name('admin.import.tables');
});
