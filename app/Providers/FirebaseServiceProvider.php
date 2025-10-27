<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FirebaseMessagingService;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FirebaseMessagingService::class, function ($app) {
            return new FirebaseMessagingService();
        });

        $this->app->alias(FirebaseMessagingService::class, 'firebase.messaging');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publier la configuration si nécessaire
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/firebase.php' => config_path('firebase.php'),
            ], 'firebase-config');
        }

        // Enregistrer les commandes artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\Firebase\SendNotificationCommand::class,
                \App\Console\Commands\Firebase\CleanupDevicesCommand::class,
                \App\Console\Commands\Firebase\ProcessScheduledNotificationsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            FirebaseMessagingService::class,
            'firebase.messaging',
        ];
    }
}
