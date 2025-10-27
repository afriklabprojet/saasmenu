<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportExportController;

/*
|--------------------------------------------------------------------------
| Import/Export Routes
|--------------------------------------------------------------------------
|
| Routes pour la gestion des imports et exports de données.
| Permet l'importation et l'exportation de menus, produits, utilisateurs,
| commandes avec support CSV, Excel, JSON et validation avancée.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Routes principales d'import/export
    Route::prefix('admin/import-export')->name('admin.import-export.')->group(function () {

        // Dashboard import/export
        Route::get('/', [ImportExportController::class, 'index'])->name('index');
        Route::get('/dashboard', [ImportExportController::class, 'dashboard'])->name('dashboard');
        Route::get('/history', [ImportExportController::class, 'history'])->name('history');
        Route::get('/templates', [ImportExportController::class, 'templates'])->name('templates');
        Route::get('/settings', [ImportExportController::class, 'settings'])->name('settings');
        Route::post('/settings', [ImportExportController::class, 'updateSettings'])->name('settings.update');

        // Import de données
        Route::prefix('import')->name('import.')->group(function () {

            // Import général
            Route::get('/', [ImportExportController::class, 'importIndex'])->name('index');
            Route::post('/upload', [ImportExportController::class, 'uploadFile'])->name('upload');
            Route::post('/validate', [ImportExportController::class, 'validateImport'])->name('validate');
            Route::post('/process', [ImportExportController::class, 'processImport'])->name('process');
            Route::get('/status/{jobId}', [ImportExportController::class, 'getImportStatus'])->name('status');
            Route::post('/cancel/{jobId}', [ImportExportController::class, 'cancelImport'])->name('cancel');

            // Import par type de données
            Route::prefix('menus')->name('menus.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importMenus'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadMenusFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateMenusImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processMenusImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadMenusTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadMenusSample'])->name('sample');
            });

            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importProducts'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadProductsFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateProductsImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processProductsImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadProductsTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadProductsSample'])->name('sample');
            });

            Route::prefix('customers')->name('customers.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importCustomers'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadCustomersFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateCustomersImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processCustomersImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadCustomersTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadCustomersSample'])->name('sample');
            });

            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importOrders'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadOrdersFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateOrdersImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processOrdersImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadOrdersTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadOrdersSample'])->name('sample');
            });

            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importCategories'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadCategoriesFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateCategoriesImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processCategoriesImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadCategoriesTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadCategoriesSample'])->name('sample');
            });

            Route::prefix('restaurants')->name('restaurants.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importRestaurants'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadRestaurantsFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateRestaurantsImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processRestaurantsImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadRestaurantsTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadRestaurantsSample'])->name('sample');
            });

            Route::prefix('coupons')->name('coupons.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importCoupons'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadCouponsFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateCouponsImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processCouponsImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadCouponsTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadCouponsSample'])->name('sample');
            });

            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [ImportExportController::class, 'importInventory'])->name('index');
                Route::post('/upload', [ImportExportController::class, 'uploadInventoryFile'])->name('upload');
                Route::post('/validate', [ImportExportController::class, 'validateInventoryImport'])->name('validate');
                Route::post('/process', [ImportExportController::class, 'processInventoryImport'])->name('process');
                Route::get('/template', [ImportExportController::class, 'downloadInventoryTemplate'])->name('template');
                Route::get('/sample', [ImportExportController::class, 'downloadInventorySample'])->name('sample');
            });
        });

        // Export de données
        Route::prefix('export')->name('export.')->group(function () {

            // Export général
            Route::get('/', [ImportExportController::class, 'exportIndex'])->name('index');
            Route::post('/generate', [ImportExportController::class, 'generateExport'])->name('generate');
            Route::get('/download/{exportId}', [ImportExportController::class, 'downloadExport'])->name('download');
            Route::get('/status/{jobId}', [ImportExportController::class, 'getExportStatus'])->name('status');
            Route::post('/cancel/{jobId}', [ImportExportController::class, 'cancelExport'])->name('cancel');

            // Export par type de données
            Route::prefix('menus')->name('menus.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportMenus'])->name('index');
                Route::post('/generate', [ImportExportController::class, 'generateMenusExport'])->name('generate');
                Route::get('/download', [ImportExportController::class, 'downloadMenusExport'])->name('download');
            });

            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportProducts'])->name('index');
                Route::post('/generate', [ImportExportController::class, 'generateProductsExport'])->name('generate');
                Route::get('/download', [ImportExportController::class, 'downloadProductsExport'])->name('download');
            });

            Route::prefix('customers')->name('customers.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportCustomers'])->name('index');
                Route::post('/generate', [ImportExportController::class, 'generateCustomersExport'])->name('generate');
                Route::get('/download', [ImportExportController::class, 'downloadCustomersExport'])->name('download');
            });

            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportOrders'])->name('index');
                Route::post('/generate', [ImportExportController::class, 'generateOrdersExport'])->name('generate');
                Route::get('/download', [ImportExportController::class, 'downloadOrdersExport'])->name('download');
            });

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportReports'])->name('index');
                Route::post('/sales', [ImportExportController::class, 'generateSalesReport'])->name('sales');
                Route::post('/customers', [ImportExportController::class, 'generateCustomersReport'])->name('customers');
                Route::post('/inventory', [ImportExportController::class, 'generateInventoryReport'])->name('inventory');
                Route::post('/financial', [ImportExportController::class, 'generateFinancialReport'])->name('financial');
            });

            Route::prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportAnalytics'])->name('index');
                Route::post('/performance', [ImportExportController::class, 'generatePerformanceReport'])->name('performance');
                Route::post('/engagement', [ImportExportController::class, 'generateEngagementReport'])->name('engagement');
                Route::post('/trends', [ImportExportController::class, 'generateTrendsReport'])->name('trends');
            });

            Route::prefix('backup')->name('backup.')->group(function () {
                Route::get('/', [ImportExportController::class, 'exportBackup'])->name('index');
                Route::post('/full', [ImportExportController::class, 'generateFullBackup'])->name('full');
                Route::post('/partial', [ImportExportController::class, 'generatePartialBackup'])->name('partial');
                Route::post('/incremental', [ImportExportController::class, 'generateIncrementalBackup'])->name('incremental');
            });
        });

        // Gestion des jobs et tâches
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [ImportExportController::class, 'jobsIndex'])->name('index');
            Route::get('/{jobId}', [ImportExportController::class, 'showJob'])->name('show');
            Route::post('/{jobId}/retry', [ImportExportController::class, 'retryJob'])->name('retry');
            Route::delete('/{jobId}', [ImportExportController::class, 'deleteJob'])->name('delete');
            Route::post('/cleanup', [ImportExportController::class, 'cleanupJobs'])->name('cleanup');
        });

        // Mappings et transformations
        Route::prefix('mappings')->name('mappings.')->group(function () {
            Route::get('/', [ImportExportController::class, 'mappingsIndex'])->name('index');
            Route::post('/create', [ImportExportController::class, 'createMapping'])->name('create');
            Route::get('/{id}', [ImportExportController::class, 'showMapping'])->name('show');
            Route::put('/{id}', [ImportExportController::class, 'updateMapping'])->name('update');
            Route::delete('/{id}', [ImportExportController::class, 'deleteMapping'])->name('delete');
            Route::post('/{id}/apply', [ImportExportController::class, 'applyMapping'])->name('apply');
        });

        // Validation et preview
        Route::prefix('validation')->name('validation.')->group(function () {
            Route::post('/file', [ImportExportController::class, 'validateFile'])->name('file');
            Route::post('/data', [ImportExportController::class, 'validateData'])->name('data');
            Route::get('/rules', [ImportExportController::class, 'getValidationRules'])->name('rules');
            Route::post('/rules', [ImportExportController::class, 'updateValidationRules'])->name('rules.update');
            Route::get('/preview/{uploadId}', [ImportExportController::class, 'previewImport'])->name('preview');
        });

        // Transformations de données
        Route::prefix('transform')->name('transform.')->group(function () {
            Route::post('/apply', [ImportExportController::class, 'applyTransformations'])->name('apply');
            Route::get('/templates', [ImportExportController::class, 'getTransformTemplates'])->name('templates');
            Route::post('/templates', [ImportExportController::class, 'saveTransformTemplate'])->name('templates.save');
            Route::delete('/templates/{id}', [ImportExportController::class, 'deleteTransformTemplate'])->name('templates.delete');
        });

        // Batch operations
        Route::prefix('batch')->name('batch.')->group(function () {
            Route::post('/process', [ImportExportController::class, 'processBatch'])->name('process');
            Route::get('/status/{batchId}', [ImportExportController::class, 'getBatchStatus'])->name('status');
            Route::post('/pause/{batchId}', [ImportExportController::class, 'pauseBatch'])->name('pause');
            Route::post('/resume/{batchId}', [ImportExportController::class, 'resumeBatch'])->name('resume');
            Route::post('/cancel/{batchId}', [ImportExportController::class, 'cancelBatch'])->name('cancel');
        });

        // Logs et monitoring
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [ImportExportController::class, 'logsIndex'])->name('index');
            Route::get('/import', [ImportExportController::class, 'importLogs'])->name('import');
            Route::get('/export', [ImportExportController::class, 'exportLogs'])->name('export');
            Route::get('/errors', [ImportExportController::class, 'errorLogs'])->name('errors');
            Route::get('/download/{logId}', [ImportExportController::class, 'downloadLog'])->name('download');
            Route::delete('/clear', [ImportExportController::class, 'clearLogs'])->name('clear');
        });

        // Scheduling et automatisation
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [ImportExportController::class, 'scheduleIndex'])->name('index');
            Route::post('/create', [ImportExportController::class, 'createSchedule'])->name('create');
            Route::get('/{id}', [ImportExportController::class, 'showSchedule'])->name('show');
            Route::put('/{id}', [ImportExportController::class, 'updateSchedule'])->name('update');
            Route::delete('/{id}', [ImportExportController::class, 'deleteSchedule'])->name('delete');
            Route::post('/{id}/activate', [ImportExportController::class, 'activateSchedule'])->name('activate');
            Route::post('/{id}/deactivate', [ImportExportController::class, 'deactivateSchedule'])->name('deactivate');
        });
    });

    // API routes pour AJAX
    Route::prefix('api/import-export')->name('api.import-export.')->group(function () {
        Route::get('/progress/{jobId}', [ImportExportController::class, 'getProgress'])->name('progress');
        Route::get('/stats', [ImportExportController::class, 'getStats'])->name('stats');
        Route::get('/queue-status', [ImportExportController::class, 'getQueueStatus'])->name('queue.status');
        Route::post('/validate-field', [ImportExportController::class, 'validateField'])->name('validate.field');
        Route::get('/suggestions/{field}', [ImportExportController::class, 'getFieldSuggestions'])->name('suggestions');
    });

    // Routes pour les webhooks
    Route::prefix('webhooks/import-export')->name('webhooks.import-export.')->group(function () {
        Route::post('/job-completed', [ImportExportController::class, 'handleJobCompleted'])->name('job.completed');
        Route::post('/job-failed', [ImportExportController::class, 'handleJobFailed'])->name('job.failed');
        Route::post('/progress-update', [ImportExportController::class, 'handleProgressUpdate'])->name('progress.update');
    });
});

// Routes publiques pour templates et exemples
Route::prefix('public/import-export')->name('public.import-export.')->group(function () {
    Route::get('/templates/{type}', [ImportExportController::class, 'downloadPublicTemplate'])->name('template');
    Route::get('/samples/{type}', [ImportExportController::class, 'downloadPublicSample'])->name('sample');
    Route::get('/documentation', [ImportExportController::class, 'getDocumentation'])->name('documentation');
    Route::get('/formats', [ImportExportController::class, 'getSupportedFormats'])->name('formats');
});
