<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemMonitoringService
{
    /**
     * Collecter et logger les métriques système
     */
    public function collectMetrics()
    {
        $metrics = [
            'timestamp' => now()->toISOString(),
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'storage' => $this->getStorageMetrics(),
            'cache' => $this->getCacheMetrics(),
            'application' => $this->getApplicationMetrics()
        ];

        Log::channel('performance')->info('Métriques système collectées', $metrics);

        // Alertes si métriques critiques
        $this->checkCriticalMetrics($metrics);

        return $metrics;
    }

    /**
     * Métriques système
     */
    private function getSystemMetrics()
    {
        return [
            'memory_usage' => [
                'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'limit_mb' => ini_get('memory_limit')
            ],
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_load' => sys_getloadavg() ?: ['N/A', 'N/A', 'N/A'],
            'disk_free_space_gb' => round(disk_free_space('/') / 1024 / 1024 / 1024, 2)
        ];
    }

    /**
     * Métriques base de données
     */
    private function getDatabaseMetrics()
    {
        try {
            $start = microtime(true);
            $connectionTest = DB::select('SELECT 1 as test');
            $connectionTime = round((microtime(true) - $start) * 1000, 2);

            // Statistiques tables principales
            $userCount = DB::table('users')->count();
            $orderCount = DB::table('orders')->count();
            $vendorCount = DB::table('users')->where('type', 2)->count();

            return [
                'connection_time_ms' => $connectionTime,
                'status' => 'connected',
                'stats' => [
                    'total_users' => $userCount,
                    'total_orders' => $orderCount,
                    'total_vendors' => $vendorCount
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Métriques stockage
     */
    private function getStorageMetrics()
    {
        try {
            $logSize = $this->getDirectorySize(storage_path('logs'));
            $cacheSize = $this->getDirectorySize(storage_path('framework/cache'));
            $sessionSize = $this->getDirectorySize(storage_path('framework/sessions'));

            return [
                'logs_size_mb' => round($logSize / 1024 / 1024, 2),
                'cache_size_mb' => round($cacheSize / 1024 / 1024, 2),
                'sessions_size_mb' => round($sessionSize / 1024 / 1024, 2),
                'public_uploads_status' => Storage::disk('public')->exists('uploads') ? 'accessible' : 'error'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Métriques cache
     */
    private function getCacheMetrics()
    {
        try {
            $start = microtime(true);
            Cache::put('monitor_test', 'ok', 60);
            $cacheTest = Cache::get('monitor_test');
            $cacheTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'response_time_ms' => $cacheTime,
                'status' => $cacheTest === 'ok' ? 'working' : 'error',
                'driver' => config('cache.default')
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Métriques application
     */
    private function getApplicationMetrics()
    {
        return [
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver')
        ];
    }

    /**
     * Vérifier métriques critiques et envoyer alertes
     */
    private function checkCriticalMetrics($metrics)
    {
        $alerts = [];

        // Alerte mémoire > 80% de la limite
        $memoryUsage = $metrics['system']['memory_usage']['current_mb'];
        $memoryLimit = (int) str_replace('M', '', $metrics['system']['memory_usage']['limit_mb']);

        if ($memoryLimit > 0 && ($memoryUsage / $memoryLimit) > 0.8) {
            $alerts[] = "Utilisation mémoire critique: {$memoryUsage}MB / {$memoryLimit}MB";
        }

        // Alerte temps de réponse DB > 1s
        if (isset($metrics['database']['connection_time_ms']) && $metrics['database']['connection_time_ms'] > 1000) {
            $alerts[] = "Temps de réponse base de données élevé: {$metrics['database']['connection_time_ms']}ms";
        }

        // Alerte espace disque < 1GB
        if ($metrics['system']['disk_free_space_gb'] < 1) {
            $alerts[] = "Espace disque critique: {$metrics['system']['disk_free_space_gb']}GB restants";
        }

        // Logger les alertes
        foreach ($alerts as $alert) {
            Log::channel('security')->critical('Alerte système critique', [
                'alert' => $alert,
                'metrics' => $metrics,
                'timestamp' => now()->toISOString()
            ]);
        }

        return $alerts;
    }

    /**
     * Calculer taille d'un répertoire
     */
    private function getDirectorySize($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }
}
