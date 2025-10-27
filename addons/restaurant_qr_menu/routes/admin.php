<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QrMenuController;
use App\Http\Controllers\Admin\QrMenuDesignController;

/*
|--------------------------------------------------------------------------
| QR Menu Admin Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth', 'admin'],
    'as' => 'admin.'
], function () {

    // Routes CRUD pour les QR Menus
    Route::resource('qr-menu', QrMenuController::class)->names([
        'index' => 'qr-menu.index',
        'create' => 'qr-menu.create',
        'store' => 'qr-menu.store',
        'show' => 'qr-menu.show',
        'edit' => 'qr-menu.edit',
        'update' => 'qr-menu.update',
        'destroy' => 'qr-menu.destroy',
    ]);

    // Routes additionnelles pour QR Menu
    Route::group(['prefix' => 'qr-menu'], function () {
        Route::get('{qrMenu}/download', [QrMenuController::class, 'download'])
            ->name('qr-menu.download');

        Route::post('{qrMenu}/regenerate', [QrMenuController::class, 'regenerate'])
            ->name('qr-menu.regenerate');

        Route::get('{qrMenu}/analytics', [QrMenuController::class, 'analytics'])
            ->name('qr-menu.analytics');

        Route::get('{qrMenu}/print', [QrMenuController::class, 'print'])
            ->name('qr-menu.print');
    });

    // Routes pour les designs de QR codes
    Route::resource('qr-designs', QrMenuDesignController::class)->names([
        'index' => 'qr-designs.index',
        'create' => 'qr-designs.create',
        'store' => 'qr-designs.store',
        'show' => 'qr-designs.show',
        'edit' => 'qr-designs.edit',
        'update' => 'qr-designs.update',
        'destroy' => 'qr-designs.destroy',
    ]);

    // Routes additionnelles pour les designs
    Route::group(['prefix' => 'qr-designs'], function () {
        Route::post('{qrDesign}/set-default', [QrMenuDesignController::class, 'setDefault'])
            ->name('qr-designs.set-default');

        Route::post('{qrDesign}/duplicate', [QrMenuDesignController::class, 'duplicate'])
            ->name('qr-designs.duplicate');

        Route::get('{qrDesign}/preview', [QrMenuDesignController::class, 'preview'])
            ->name('qr-designs.preview');
    });
});
