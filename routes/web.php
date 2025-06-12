<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AttachementController;
use App\Http\Controllers\ClientController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes de test pour le sélecteur client (temporaire)
Route::get('/test-client-selector', function () {
    return Inertia::render('TestClientSelector');
})->name('test.client-selector');

// Route de test pour le nom signataire (temporaire)
Route::get('/test-signataire', function () {
    return Inertia::render('TestSignataire');
})->name('test.signataire');

// Routes API temporaires sans auth pour test
Route::get('/test/api/clients', [ClientController::class, 'apiList'])->name('test.clients.list');
Route::get('/test/api/clients/search', [ClientController::class, 'apiSearch'])->name('test.clients.search');

Route::middleware(['auth'])->group(function () {
    // Routes pour les attachements de travaux
    Route::get('/attachements', [AttachementController::class, 'index'])->name('attachements.index');
    Route::get('/attachements/create', [AttachementController::class, 'create'])->name('attachements.create');
    Route::post('/attachements', [AttachementController::class, 'store'])->name('attachements.store');
    Route::get('/attachements/{attachement}', [AttachementController::class, 'show'])->name('attachements.show');
    Route::get('/attachements/{attachement}/pdf', [AttachementController::class, 'downloadPdf'])->name('attachements.download-pdf');
    
    // Routes pour les clients
    Route::resource('clients', ClientController::class);
    Route::get('/api/clients', [ClientController::class, 'api'])->name('clients.api');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
