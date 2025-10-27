<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\MonitoringController;

/*
|--------------------------------------------------------------------------
| Monitoring Routes
|--------------------------------------------------------------------------
|
| Routes pour le système de monitoring et surveillance RestroSaaS
|
*/

Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {

    // Tableau de bord monitoring
    Route::get('/monitoring', [MonitoringController::class, 'dashboard'])->name('admin.monitoring.dashboard');

    // API endpoints monitoring
    Route::group(['prefix' => 'monitoring/api'], function () {
        Route::get('/metrics', [MonitoringController::class, 'apiMetrics'])->name('admin.monitoring.api.metrics');
        Route::get('/logs', [MonitoringController::class, 'logs'])->name('admin.monitoring.api.logs');
        Route::get('/alerts', [MonitoringController::class, 'alerts'])->name('admin.monitoring.api.alerts');
        Route::get('/health', [MonitoringController::class, 'healthCheck'])->name('admin.monitoring.api.health');
    });

});

// Health check public pour monitoring externe
Route::get('/health-check', [MonitoringController::class, 'healthCheck'])->name('public.health.check');
