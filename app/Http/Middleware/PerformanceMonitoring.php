<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $executionTime = round(($endTime - $startTime) * 1000, 2); // en millisecondes
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2); // en MB

        // Log performance si temps d'exécution > 2s ou mémoire > 50MB
        if ($executionTime > 2000 || $memoryUsed > 50) {
            Log::channel('performance')->warning('Performance dégradée détectée', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => $executionTime,
                'memory_usage_mb' => $memoryUsed,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
        }

        // Log métrique normale pour monitoring
        Log::channel('performance')->info('Requête traitée', [
            'url' => $request->path(),
            'method' => $request->method(),
            'execution_time_ms' => $executionTime,
            'memory_usage_mb' => $memoryUsed,
            'status_code' => $response->getStatusCode(),
            'timestamp' => now()->toISOString()
        ]);

        // Ajouter headers performance pour debugging
        if (config('app.debug')) {
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            $response->headers->set('X-Memory-Usage', $memoryUsed . 'MB');
        }

        return $response;
    }
}
