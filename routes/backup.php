<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\BackupController;

/*
|--------------------------------------------------------------------------
| Backup Routes
|--------------------------------------------------------------------------
|
| Routes pour le système de backup et restauration RestroSaaS
|
*/

Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {
    
    // Interface principale de gestion des backups
    Route::get('/backups', [BackupController::class, 'index'])->name('admin.backups.index');
    
    // API endpoints backups
    Route::group(['prefix' => 'backups/api'], function () {
        Route::post('/create', [BackupController::class, 'create'])->name('admin.backups.api.create');
        Route::get('/list', [BackupController::class, 'list'])->name('admin.backups.api.list');
        Route::get('/storage-status', [BackupController::class, 'storageStatus'])->name('admin.backups.api.storage');
        Route::get('/{backup}/info', [BackupController::class, 'info'])->name('admin.backups.api.info');
        Route::delete('/{backup}', [BackupController::class, 'delete'])->name('admin.backups.api.delete');
        Route::post('/{backup}/restore', [BackupController::class, 'restore'])->name('admin.backups.api.restore');
    });
    
    // Téléchargement de backups
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('admin.backups.download');
    
});