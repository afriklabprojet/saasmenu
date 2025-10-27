<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationMiddleware
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Surveiller les erreurs critiques
        if ($response->getStatusCode() >= 500) {
            $this->notificationService->sendSystemAlert(
                'error',
                "Erreur HTTP {$response->getStatusCode()} sur {$request->fullUrl()}",
                [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => $response->getStatusCode()
                ]
            );
        }

        // Surveiller les temps de réponse lents
        $responseTime = microtime(true) - LARAVEL_START;
        if ($responseTime > 5.0) { // Plus de 5 secondes
            $this->notificationService->notifyPerformanceIssue(
                'response_time',
                round($responseTime * 1000), // en ms
                5000 // seuil 5s
            );
        }

        // Surveiller les tentatives d'accès admin suspects
        if ($request->is('admin/*') && !Auth::check()) {
            $this->notificationService->send(
                'security_incident',
                "Tentative accès admin non autorisé depuis {$request->ip()}",
                [
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now()
                ],
                'high',
                ['email', 'database']
            );
        }

        return $response;
    }
}
