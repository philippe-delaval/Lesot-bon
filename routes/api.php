<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttachementController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes API pour les attachements
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attachements', [AttachementController::class, 'apiIndex']);
    Route::get('/attachements/{attachement}', [AttachementController::class, 'apiShow']);
}); 