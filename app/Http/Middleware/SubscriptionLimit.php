<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $feature
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $feature = null)
    {
        // Si c'est un admin super (type 1), autoriser tout
        if (Auth::user()->type == 1) {
            return $next($request);
        }

        // Obtenir l'ID du vendor
        $vendor_id = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

        // Pour le moment, autoriser tous les vendors (pour déboguer)
        // TODO: Activer la vérification une fois que tous les plans ont analytics activé
        /*
        if ($feature && !\App\Helpers\helper::checkPlanLimit($vendor_id, $feature)) {
            return redirect()->route('admin.index')->with('error', 'Cette fonctionnalité n\'est pas disponible dans votre abonnement actuel.');
        }
        */

        return $next($request);
    }
}
