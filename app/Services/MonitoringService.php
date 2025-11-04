<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Service de monitoring et métriques en temps réel
 * Collecte, analyse et alerte sur les performances système
 */
class MonitoringService
{
    private $metricsCache = 'system_metrics';
    private $alertsCache = 'system_alerts';

    /**
     * Enregistrer une métrique de performance
     */
    public function recordMetric(string $type, string $action, float $duration, array $context = []): void
    {
        $metric = [
            'type' => $type,
            'action' => $action,
            'duration' => $duration,
            'timestamp' => Carbon::now()->toISOString(),
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];

        // Log structuré
        Log::channel('metrics')->info('Performance Metric', $metric);

        // Stocker en cache pour dashboard temps réel
        $this->storeMetricInCache($metric);

        // Vérifier les seuils d'alerte
        $this->checkPerformanceAlerts($type, $action, $duration);
    }

    /**
     * Enregistrer un événement de sécurité
     */
    public function recordSecurityEvent(string $event, string $severity, array $data, ?Request $request = null): void
    {
        $securityLog = [
            'event' => $event,
            'severity' => $severity,
            'timestamp' => Carbon::now()->toISOString(),
            'data' => $data,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'user_id' => auth()->id()
        ];

        Log::channel('security')->warning('Security Event', $securityLog);

        // Alertes immédiates pour événements critiques
        if (in_array($severity, ['high', 'critical'])) {
            $this->triggerSecurityAlert($securityLog);
        }
    }

    /**
     * Surveiller les erreurs système
     */
    public function recordError(\Throwable $exception, array $context = []): void
    {
        $errorData = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
            'timestamp' => Carbon::now()->toISOString(),
            'memory_usage' => memory_get_usage(true),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'user_id' => auth()?->id()
        ];

        Log::channel('errors')->error('System Error', $errorData);

        // Compter les erreurs pour détecter les pics
        $this->incrementErrorCounter($exception->getFile(), $exception->getLine());
    }

    /**
     * Monitorer l'activité utilisateur
     */
    public function recordUserActivity(string $action, array $data = []): void
    {
        $activity = [
            'user_id' => auth()->id(),
            'action' => $action,
            'data' => $data,
            'timestamp' => Carbon::now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        Log::channel('activity')->info('User Activity', $activity);

        // Détecter activités suspectes
        $this->detectSuspiciousActivity($activity);
    }

    /**
     * Collecter les métriques système en temps réel
     */
    public function getSystemMetrics(): array
    {
        $cacheKey = $this->metricsCache . '_' . Carbon::now()->format('Y-m-d-H-i');

        return Cache::remember($cacheKey, 60, function () {
            return [
                'database' => $this->getDatabaseMetrics(),
                'cache' => $this->getCacheMetrics(),
                'performance' => $this->getPerformanceMetrics(),
                'security' => $this->getSecurityMetrics(),
                'system' => $this->getSystemResourceMetrics(),
                'timestamp' => Carbon::now()->toISOString()
            ];
        });
    }

    /**
     * Métriques base de données
     */
    private function getDatabaseMetrics(): array
    {
        $start = microtime(true);

        // Test de connectivité
        try {
            DB::connection()->getPdo();
            $connectionStatus = 'healthy';
        } catch (\Exception $e) {
            $connectionStatus = 'error';
            $this->recordError($e, ['context' => 'database_health_check']);
        }

        $connectionTime = (microtime(true) - $start) * 1000;

        return [
            'connection_status' => $connectionStatus,
            'connection_time_ms' => round($connectionTime, 2),
            'active_connections' => $this->getActiveConnections(),
            'slow_queries' => $this->getSlowQueriesCount(),
            'query_cache_hit_rate' => $this->getQueryCacheHitRate()
        ];
    }

    /**
     * Métriques cache
     */
    private function getCacheMetrics(): array
    {
        $stats = Cache::getStore();

        return [
            'status' => 'healthy',
            'hit_rate' => $this->calculateCacheHitRate(),
            'total_keys' => $this->getCacheKeyCount(),
            'memory_usage' => $this->getCacheMemoryUsage(),
            'evictions' => $this->getCacheEvictions()
        ];
    }

    /**
     * Métriques de performance
     */
    private function getPerformanceMetrics(): array
    {
        $metrics = Cache::get($this->metricsCache, []);
        $recent = array_filter($metrics, function($metric) {
            return Carbon::parse($metric['timestamp'])->gt(Carbon::now()->subMinutes(5));
        });

        if (empty($recent)) {
            return ['avg_response_time' => 0, 'requests_per_minute' => 0];
        }

        $avgResponseTime = collect($recent)->avg('duration');
        $requestsPerMinute = count($recent);

        return [
            'avg_response_time_ms' => round($avgResponseTime, 2),
            'requests_per_minute' => $requestsPerMinute,
            'peak_memory_mb' => round(max(array_column($recent, 'peak_memory')) / 1024 / 1024, 2),
            'avg_memory_mb' => round(collect($recent)->avg('memory_usage') / 1024 / 1024, 2)
        ];
    }

    /**
     * Métriques de sécurité
     */
    private function getSecurityMetrics(): array
    {
        return [
            'failed_logins_last_hour' => $this->getFailedLoginsCount(),
            'blocked_ips' => $this->getBlockedIpsCount(),
            'security_events_today' => $this->getSecurityEventsCount(),
            'suspicious_activities' => $this->getSuspiciousActivitiesCount()
        ];
    }

    /**
     * Métriques ressources système
     */
    private function getSystemResourceMetrics(): array
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'memory_limit_mb' => $this->getMemoryLimitMB(),
            'disk_usage' => $this->getDiskUsage(),
            'uptime' => $this->getSystemUptime()
        ];
    }

    /**
     * Stocker métrique en cache pour dashboard
     */
    private function storeMetricInCache(array $metric): void
    {
        $metrics = Cache::get($this->metricsCache, []);
        $metrics[] = $metric;

        // Garder seulement les 1000 dernières métriques
        if (count($metrics) > 1000) {
            $metrics = array_slice($metrics, -1000);
        }

        Cache::put($this->metricsCache, $metrics, 3600); // 1 heure
    }

    /**
     * Vérifier les seuils d'alerte performance
     */
    private function checkPerformanceAlerts(string $type, string $action, float $duration): void
    {
        $thresholds = [
            'http_request' => 2000, // 2 secondes
            'database_query' => 1000, // 1 seconde
            'cache_operation' => 100, // 100ms
            'file_operation' => 500   // 500ms
        ];

        if (isset($thresholds[$type]) && $duration > $thresholds[$type]) {
            $this->triggerPerformanceAlert([
                'type' => $type,
                'action' => $action,
                'duration' => $duration,
                'threshold' => $thresholds[$type],
                'severity' => $duration > ($thresholds[$type] * 2) ? 'high' : 'medium'
            ]);
        }
    }

    /**
     * Déclencher alerte de performance
     */
    private function triggerPerformanceAlert(array $alertData): void
    {
        Log::channel('alerts')->warning('Performance Alert', $alertData);

        // Stocker l'alerte pour le dashboard
        $alerts = Cache::get($this->alertsCache, []);
        $alerts[] = array_merge($alertData, [
            'timestamp' => Carbon::now()->toISOString(),
            'alert_type' => 'performance'
        ]);

        Cache::put($this->alertsCache, $alerts, 86400); // 24 heures
    }

    /**
     * Déclencher alerte de sécurité
     */
    private function triggerSecurityAlert(array $securityEvent): void
    {
        Log::channel('alerts')->critical('Security Alert', $securityEvent);

        $alerts = Cache::get($this->alertsCache, []);
        $alerts[] = array_merge($securityEvent, [
            'alert_type' => 'security',
            'requires_action' => true
        ]);

        Cache::put($this->alertsCache, $alerts, 86400);
    }

    /**
     * Détecter activité suspecte
     */
    private function detectSuspiciousActivity(array $activity): void
    {
        $userId = $activity['user_id'];
        $ip = $activity['ip_address'];

        // Vérifier les tentatives répétées
        $recentAttempts = $this->getRecentAttempts($userId, $ip);

        if ($recentAttempts > 10) { // Plus de 10 actions en 1 minute
            $this->recordSecurityEvent(
                'suspicious_activity_rate',
                'medium',
                [
                    'user_id' => $userId,
                    'ip_address' => $ip,
                    'attempts_count' => $recentAttempts,
                    'timeframe' => '1_minute'
                ]
            );
        }
    }

    /**
     * Incrémenter compteur d'erreurs
     */
    private function incrementErrorCounter(string $file, int $line): void
    {
        $key = 'error_count_' . md5($file . ':' . $line);
        $count = Cache::increment($key, 1);

        if ($count === false) {
            Cache::put($key, 1, 3600); // 1 heure
            $count = 1;
        }

        // Alerte si trop d'erreurs au même endroit
        if ($count > 5) {
            $this->triggerPerformanceAlert([
                'type' => 'error_spike',
                'file' => $file,
                'line' => $line,
                'count' => $count,
                'severity' => 'high'
            ]);
        }
    }

    /**
     * Obtenir les alertes actives
     */
    public function getActiveAlerts(): array
    {
        $alerts = Cache::get($this->alertsCache, []);

        // Filtrer les alertes des dernières 24h
        return array_filter($alerts, function($alert) {
            return Carbon::parse($alert['timestamp'])->gt(Carbon::now()->subDay());
        });
    }

    /**
     * Générer rapport de santé système
     */
    public function getHealthReport(): array
    {
        $metrics = $this->getSystemMetrics();
        $alerts = $this->getActiveAlerts();

        $health = [
            'overall_status' => 'healthy',
            'score' => 100,
            'components' => []
        ];

        // Évaluer chaque composant
        foreach (['database', 'cache', 'performance', 'security'] as $component) {
            $componentHealth = $this->evaluateComponentHealth($component, $metrics[$component] ?? []);
            $health['components'][$component] = $componentHealth;

            if ($componentHealth['status'] !== 'healthy') {
                $health['overall_status'] = 'degraded';
                $health['score'] -= $componentHealth['impact'];
            }
        }

        // Ajuster le score selon les alertes
        $criticalAlerts = array_filter($alerts, fn($a) => $a['severity'] === 'critical');
        $health['score'] -= count($criticalAlerts) * 20;

        if ($health['score'] < 50) {
            $health['overall_status'] = 'critical';
        } elseif ($health['score'] < 80) {
            $health['overall_status'] = 'degraded';
        }

        $health['alerts_count'] = count($alerts);
        $health['last_check'] = Carbon::now()->toISOString();

        return $health;
    }

    /**
     * Évaluer la santé d'un composant
     */
    private function evaluateComponentHealth(string $component, array $metrics): array
    {
        switch ($component) {
            case 'database':
                if (($metrics['connection_status'] ?? '') !== 'healthy') {
                    return ['status' => 'critical', 'impact' => 50, 'message' => 'Database connection failed'];
                }
                if (($metrics['connection_time_ms'] ?? 0) > 1000) {
                    return ['status' => 'degraded', 'impact' => 20, 'message' => 'Slow database connection'];
                }
                return ['status' => 'healthy', 'impact' => 0, 'message' => 'Database operating normally'];

            case 'performance':
                if (($metrics['avg_response_time_ms'] ?? 0) > 2000) {
                    return ['status' => 'degraded', 'impact' => 30, 'message' => 'High response times'];
                }
                return ['status' => 'healthy', 'impact' => 0, 'message' => 'Performance within normal range'];

            default:
                return ['status' => 'healthy', 'impact' => 0, 'message' => 'Component healthy'];
        }
    }

    // Méthodes utilitaires (à implémenter selon l'environnement)
    private function getActiveConnections(): int { return rand(5, 20); }
    private function getSlowQueriesCount(): int { return rand(0, 5); }
    private function getQueryCacheHitRate(): float { return rand(85, 98) / 100; }
    private function calculateCacheHitRate(): float { return rand(90, 99) / 100; }
    private function getCacheKeyCount(): int { return rand(100, 1000); }
    private function getCacheMemoryUsage(): int { return rand(50, 200); }
    private function getCacheEvictions(): int { return rand(0, 10); }
    private function getFailedLoginsCount(): int { return rand(0, 20); }
    private function getBlockedIpsCount(): int { return rand(0, 5); }
    private function getSecurityEventsCount(): int { return rand(0, 10); }
    private function getSuspiciousActivitiesCount(): int { return rand(0, 3); }
    private function getMemoryLimitMB(): int { return 512; }
    private function getDiskUsage(): string { return rand(30, 80) . '%'; }
    private function getSystemUptime(): string { return rand(1, 30) . ' days'; }
    private function getRecentAttempts(int $userId, string $ip): int { return rand(1, 15); }
}
