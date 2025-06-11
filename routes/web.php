<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AttachementController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes pour les attachements de travaux
Route::middleware(['auth'])->group(function () {
    Route::get('/attachements', [AttachementController::class, 'index'])->name('attachements.index');
    Route::get('/attachements/create', [AttachementController::class, 'create'])->name('attachements.create');
    Route::post('/attachements', [AttachementController::class, 'store'])->name('attachements.store');
    Route::get('/attachements/{attachement}', [AttachementController::class, 'show'])->name('attachements.show');
    Route::get('/attachements/{attachement}/pdf', [AttachementController::class, 'downloadPdf'])->name('attachements.download-pdf');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
