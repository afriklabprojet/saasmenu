<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CustomDomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);

        // Si c'est le domaine principal, continuer normalement
        if ($host === $appDomain || $host === 'localhost' || $host === '127.0.0.1') {
            return $next($request);
        }

        // Chercher le restaurant/café par domaine personnalisé
        $vendor = User::where('custom_domain', $host)
            ->where('domain_verified', true)
            ->where('type', 2) // Type vendor
            ->where('is_available', 1)
            ->where('is_deleted', 2)
            ->first();

        if (!$vendor) {
            // Domaine non trouvé ou non vérifié
            abort(404, 'Restaurant not found or domain not verified');
        }

        // Stocker le vendor dans la requête pour utilisation ultérieure
        $request->merge(['custom_domain_vendor' => $vendor]);
        $request->attributes->set('vendor_slug', $vendor->slug);
        $request->attributes->set('vendor_id', $vendor->id);

        // Rediriger vers la page du restaurant
        if ($request->path() === '/') {
            return redirect()->route('front.restaurant', ['slug' => $vendor->slug]);
        }

        return $next($request);
    }
}
