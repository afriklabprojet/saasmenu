<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ImportExportService;

class ImportExportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImportExportService::class, function ($app) {
            return new ImportExportService();
        });

        $this->app->alias(ImportExportService::class, 'import.export');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier la configuration si nécessaire
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/import-export.php' => config_path('import-export.php'),
            ], 'import-export-config');
        }

        // Enregistrer les commandes artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ImportExport\ProcessImportCommand::class,
                \App\Console\Commands\ImportExport\ProcessExportCommand::class,
                \App\Console\Commands\ImportExport\CleanupFilesCommand::class,
                \App\Console\Commands\ImportExport\ProcessScheduledJobsCommand::class,
            ]);
        }

        // Enregistrer les vues
        $this->loadViewsFrom(__DIR__.'/../../resources/views/import-export', 'import-export');

        // Publier les vues
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../resources/views/import-export' => resource_path('views/vendor/import-export'),
            ], 'import-export-views');
        }

        // Publier les assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../resources/js/import-export' => resource_path('js/vendor/import-export'),
                __DIR__.'/../../resources/css/import-export' => resource_path('css/vendor/import-export'),
            ], 'import-export-assets');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ImportExportService::class,
            'import.export',
        ];
    }
}
