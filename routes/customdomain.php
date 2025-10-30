<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomDomainController;

Route::group(['namespace' => 'App\Http\Controllers\Admin', 'prefix' => 'admin', 'middleware' => 'auth'], function () {

    // Routes pour Custom Domain - accessible selon les limites d'abonnement
    Route::group(['prefix' => 'custom_domain', 'middleware' => 'subscription.limit:custom_domain'], function () {

        // Page principale du domaine personnalisé
        Route::get('/', [CustomDomainController::class, 'index'])->name('admin.custom-domain.index');

        // Enregistrer/mettre à jour le domaine
        Route::post('/store', [CustomDomainController::class, 'store'])->name('admin.custom-domain.store');

        // Vérifier le domaine
        Route::post('/verify', [CustomDomainController::class, 'verify'])->name('admin.custom-domain.verify');

        // Supprimer le domaine
        Route::delete('/delete', [CustomDomainController::class, 'delete'])->name('admin.custom-domain.delete');

        // Réactiver un domaine
        Route::post('/reactivate', [CustomDomainController::class, 'reactivate'])->name('admin.custom-domain.reactivate');

        // Aide et documentation
        Route::get('/help', [CustomDomainController::class, 'help'])->name('admin.custom-domain.help');
    });
});
