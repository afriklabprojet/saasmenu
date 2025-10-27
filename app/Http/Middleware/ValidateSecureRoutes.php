<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateSecureRoutes
{
    /**
     * Routes sensibles nécessitant HTTPS en production
     */
    private $secureRoutes = [
        'admin*',
        'login*',
        'register*',
        'payment*',
        'checkout*',
        'api/payment*',
        'password*'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Force HTTPS pour routes sensibles en production uniquement (désactivé en local)
        if (env('FORCE_HTTPS', false) && env('APP_ENV') === 'production' && !$request->secure()) {
            foreach ($this->secureRoutes as $pattern) {
                if ($request->is($pattern)) {
                    return redirect()->secure($request->getRequestUri(), 301);
                }
            }
        }

        return $next($request);
    }
}
