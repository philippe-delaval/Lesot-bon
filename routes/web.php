<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AttachementController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\PlanningController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

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
    
    // Routes pour les demandes
    Route::resource('demandes', DemandeController::class);
    Route::post('demandes/{demande}/assign', [DemandeController::class, 'assign'])->name('demandes.assign');
    Route::post('demandes/{demande}/complete', [DemandeController::class, 'complete'])->name('demandes.complete');
    Route::post('demandes/{demande}/convert', [DemandeController::class, 'convertToAttachement'])->name('demandes.convert');
    
    // Routes pour les employés
    Route::resource('employes', EmployeController::class);
    Route::get('/api/employes', [EmployeController::class, 'apiList'])->name('employes.api');
    Route::get('/api/employes/search', [EmployeController::class, 'apiSearch'])->name('employes.search');
    
    // Routes pour les équipes
    Route::resource('equipes', EquipeController::class);
    Route::post('equipes/{equipe}/employes', [EquipeController::class, 'ajouterEmploye'])->name('equipes.ajouter-employe');
    Route::delete('equipes/{equipe}/employes/{employe}', [EquipeController::class, 'retirerEmploye'])->name('equipes.retirer-employe');
    Route::get('/api/equipes', [EquipeController::class, 'apiList'])->name('equipes.api');
    
    // Routes pour le planning
    Route::resource('planning', PlanningController::class);
    Route::post('planning/{planning}/demarrer', [PlanningController::class, 'demarrer'])->name('planning.demarrer');
    Route::post('planning/{planning}/terminer', [PlanningController::class, 'terminer'])->name('planning.terminer');
    Route::post('planning/{planning}/valider', [PlanningController::class, 'valider'])->name('planning.valider');
    Route::get('/api/planning/calendrier', [PlanningController::class, 'calendrierData'])->name('planning.calendrier-data');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
