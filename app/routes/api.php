<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OwnerController;

Route::post('/owners', [OwnerController::class, 'store']);