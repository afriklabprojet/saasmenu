<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AddonRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $addon, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request, $addon);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Résoudre la signature de la requête pour le rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $addon): string
    {
        $user = $request->user();
        $restaurant = $request->get('restaurant');

        // Utiliser différentes clés selon le contexte
        if ($user) {
            $identifier = "user:{$user->id}";
        } else {
            $identifier = "ip:" . $request->ip();
        }

        if ($restaurant) {
            $identifier .= ":restaurant:{$restaurant->id}";
        }

        return "addon_rate_limit:{$addon}:{$identifier}";
    }

    /**
     * Créer une réponse "Too Many Attempts"
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts)
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Trop de requêtes. Veuillez patienter avant de réessayer.',
            'retry_after' => $retryAfter,
            'limit' => $maxAttempts
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Calculer le nombre de tentatives restantes
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        $attempts = RateLimiter::attempts($key);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Ajouter les headers de rate limiting à la réponse
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }
}
