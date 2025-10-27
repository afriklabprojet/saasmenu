<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocalizationController;

/*
|--------------------------------------------------------------------------
| Multi-Language Addon Routes
|--------------------------------------------------------------------------
|
| Routes pour la gestion multilingue de l'application
|
*/

// Route pour changer la langue
Route::prefix('lang')->name('lang.')->group(function () {
    Route::post('change', [LocalizationController::class, 'changeLocale'])->name('change');
    Route::get('current', [LocalizationController::class, 'getCurrentLocale'])->name('current');
    Route::get('supported', [LocalizationController::class, 'getSupportedLocales'])->name('supported');
});

// Routes pour l'administration de la localisation
Route::prefix('admin')->middleware(['AuthMiddleware'])->name('admin.')->group(function () {
    Route::prefix('localization')->name('localization.')->group(function () {
        Route::get('/', [LocalizationController::class, 'index'])->name('index');
        Route::get('stats', [LocalizationController::class, 'getStats'])->name('stats');
        Route::get('test-translations', [LocalizationController::class, 'testTranslations'])->name('test');
    });
});
