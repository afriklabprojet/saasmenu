<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\MonitoringService;
use Carbon\Carbon;

/**
 * Middleware de monitoring automatique des requêtes HTTP
 * Capture les métriques de performance et événements de sécurité
 */
class MonitoringMiddleware
{
    protected $monitoring;
    
    public function __construct(MonitoringService $monitoring)
    {
        $this->monitoring = $monitoring;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Enregistrer l'activité utilisateur si authentifié
        if (auth()->check()) {
            $this->monitoring->recordUserActivity('http_request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route' => $request->route()?->getName()
            ]);
        }
        
        // Détecter les tentatives d'attaque
        $this->detectSecurityThreats($request);
        
        try {
            $response = $next($request);
            
            // Enregistrer les métriques de performance
            $this->recordPerformanceMetrics($request, $response, $startTime, $startMemory);
            
            return $response;
            
        } catch (\Throwable $exception) {
            // Enregistrer l'erreur
            $this->monitoring->recordError($exception, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);
            
            throw $exception;
        }
    }
    
    /**
     * Enregistrer les métriques de performance
     */
    private function recordPerformanceMetrics(Request $request, Response $response, float $startTime, int $startMemory): void
    {
        $duration = (microtime(true) - $startTime) * 1000; // en millisecondes
        $memoryUsed = memory_get_usage(true) - $startMemory;
        
        $context = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'route' => $request->route()?->getName(),
            'memory_used_bytes' => $memoryUsed,
            'query_count' => $this->getQueryCount(),
            'user_id' => auth()->id(),
            'ip' => $request->ip()
        ];
        
        $this->monitoring->recordMetric('http_request', $request->route()?->getName() ?? 'unknown', $duration, $context);
        
        // Alertes spécifiques
        if ($duration > 5000) { // > 5 secondes
            $this->monitoring->recordSecurityEvent(
                'slow_request',
                'medium',
                array_merge($context, ['duration_ms' => $duration]),
                $request
            );
        }
        
        if ($response->getStatusCode() >= 500) {
            $this->monitoring->recordSecurityEvent(
                'server_error',
                'high',
                $context,
                $request
            );
        }
    }
    
    /**
     * Détecter les menaces de sécurité
     */
    private function detectSecurityThreats(Request $request): void
    {
        // Détection d'injection SQL
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+set/i',
            '/<script/i',
            '/javascript:/i'
        ];
        
        $allInput = json_encode($request->all());
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $allInput)) {
                $this->monitoring->recordSecurityEvent(
                    'sql_injection_attempt',
                    'critical',
                    [
                        'pattern_matched' => $pattern,
                        'input_data' => $request->all(),
                        'user_agent' => $request->userAgent()
                    ],
                    $request
                );
                break;
            }
        }
        
        // Détection de scan de vulnérabilités
        $suspiciousUA = [
            'sqlmap',
            'nikto',
            'dirbuster',
            'acunetix',
            'nessus'
        ];
        
        $userAgent = strtolower($request->userAgent() ?? '');
        foreach ($suspiciousUA as $scanner) {
            if (strpos($userAgent, $scanner) !== false) {
                $this->monitoring->recordSecurityEvent(
                    'vulnerability_scan',
                    'high',
                    ['user_agent' => $request->userAgent()],
                    $request
                );
                break;
            }
        }
        
        // Détection de tentatives d'accès à des fichiers sensibles
        $sensitivePaths = [
            '/.env',
            '/config.php',
            '/wp-config.php',
            '/admin.php',
            '/.git/',
            '/backup',
            '/database'
        ];
        
        $path = $request->getPathInfo();
        foreach ($sensitivePaths as $sensitivePath) {
            if (strpos($path, $sensitivePath) !== false) {
                $this->monitoring->recordSecurityEvent(
                    'sensitive_file_access',
                    'medium',
                    ['requested_path' => $path],
                    $request
                );
                break;
            }
        }
        
        // Détection de trop nombreuses requêtes (rate limiting)
        $this->checkRateLimit($request);
    }
    
    /**
     * Vérifier les limites de taux
     */
    private function checkRateLimit(Request $request): void
    {
        $ip = $request->ip();
        $cacheKey = "rate_limit_{$ip}";
        
        $requests = cache()->get($cacheKey, 0);
        $requests++;
        
        cache()->put($cacheKey, $requests, 60); // 1 minute
        
        // Plus de 100 requêtes par minute
        if ($requests > 100) {
            $this->monitoring->recordSecurityEvent(
                'rate_limit_exceeded',
                'high',
                [
                    'requests_count' => $requests,
                    'timeframe' => '1_minute'
                ],
                $request
            );
        }
        
        // Plus de 500 requêtes par minute = attaque probable
        if ($requests > 500) {
            $this->monitoring->recordSecurityEvent(
                'ddos_attempt',
                'critical',
                [
                    'requests_count' => $requests,
                    'timeframe' => '1_minute'
                ],
                $request
            );
        }
    }
    
    /**
     * Obtenir le nombre de requêtes SQL exécutées
     */
    private function getQueryCount(): int
    {
        return count(\DB::getQueryLog());
    }
}