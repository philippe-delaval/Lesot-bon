<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttachementController;
use App\Http\Controllers\ClientController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes API pour les clients (temporairement sans auth pour les tests)
Route::get('/clients', [ClientController::class, 'apiList']);
Route::get('/clients/search', [ClientController::class, 'apiSearch']);

// Routes API pour les attachements
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attachements', [AttachementController::class, 'apiIndex']);
    Route::get('/attachements/{attachement}', [AttachementController::class, 'apiShow']);
}); 