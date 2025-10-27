<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\addons\CouponsController;

use App\Http\Controllers\web\HomeController;

/*

|--------------------------------------------------------------------------

| Web Routes

|--------------------------------------------------------------------------

|

| Here is where you can register web routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| contains the "web" middleware group. Now create something great!

|

*/



Route::group(['namespace' => 'admin', 'prefix' => 'admin'], function () {

    Route::group(['middleware' => 'AuthMiddleware'], function () {

        Route::group(['prefix' => 'coupons'], function () {

            Route::get('', [CouponsController::class, 'index'])->name('coupons');

            Route::get('/add', [CouponsController::class, 'add']);

            Route::post('/store', [CouponsController::class, 'store']);

            Route::get('/edit-{id}', [CouponsController::class, 'show']);

            Route::post('/update-{id}', [CouponsController::class, 'update']);

            Route::get('/status_change-{id}/{status}', [CouponsController::class, 'status_change']);
            Route::post('/reorder_coupon', [CouponsController::class, 'reorder_coupon']);
            Route::get('del-{id}', [CouponsController::class, 'del']);

        });
    

        Route::middleware('VendorMiddleware')->group(function () {

            Route::post('/applycoupon', [CouponsController::class, 'vendorapplypromocode']);

            Route::post('/removecoupon', [CouponsController::class, 'vendorremovepromocode']);

            Route::post('/reorder_coupon', [CouponsController::class, 'reorder_coupon']);

            Route::get('/details-{code}', [CouponsController::class, 'details']);

        });

    });

});

Route::namespace('front')->group(function () {

    
    Route::post('/cart/applypromocode', [HomeController::class, 'applypromocode']);

    Route::post('/cart/removepromocode', [HomeController::class, 'removepromocode']);

});

