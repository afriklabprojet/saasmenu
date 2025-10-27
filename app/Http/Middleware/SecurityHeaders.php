<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Force HTTPS en production uniquement (désactivé en local)
        if (env('FORCE_HTTPS', false) && env('APP_ENV') === 'production' && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        // Headers de sécurité
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // CSP pour les paiements, Google reCAPTCHA et Google Analytics
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.stripe.com *.paypal.com *.google.com *.gstatic.com *.googletagmanager.com; " .
               "style-src 'self' 'unsafe-inline' fonts.googleapis.com *.google.com *.gstatic.com; " .
               "font-src 'self' fonts.gstatic.com; " .
               "img-src 'self' data: *.stripe.com *.paypal.com *.google.com *.googletagmanager.com *.google-analytics.com; " .
               "connect-src 'self' *.stripe.com *.paypal.com *.google.com *.google-analytics.com *.googletagmanager.com; " .
               "frame-src *.stripe.com *.paypal.com *.google.com *.gstatic.com;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
