<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrMenuScanController;

/*
|--------------------------------------------------------------------------
| QR Menu Public Routes
|--------------------------------------------------------------------------
*/

// Routes publiques pour scanner les QR codes
Route::group(['prefix' => 'qr'], function () {

    // Scan direct d'un QR code
    Route::get('scan/{slug}', [QrMenuScanController::class, 'scan'])
        ->name('qr-menu.scan');

    // Page d'information avant redirection
    Route::get('info/{slug}', [QrMenuScanController::class, 'info'])
        ->name('qr-menu.info');

    // Redirection vers le menu depuis la page d'info
    Route::get('go/{slug}', [QrMenuScanController::class, 'redirect'])
        ->name('qr-menu.redirect');

    // API endpoint pour les statistiques de scan (optionnel)
    Route::post('analytics/{slug}', [QrMenuScanController::class, 'recordCustomScan'])
        ->name('qr-menu.analytics');
});
