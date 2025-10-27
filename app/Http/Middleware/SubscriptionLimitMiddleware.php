<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\PricingPlan;
use App\Models\Item;
use App\Models\Category;

class SubscriptionLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $limitType  Type de limite à vérifier (products, categories, etc.)
     */
    public function handle(Request $request, Closure $next, ?string $limitType = null): Response
    {
        // Vérifier si l'utilisateur est connecté et est un vendor
        if (!Auth::check() || Auth::user()->type != 2) {
            return $next($request);
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur a un plan
        if (!$user->plan_id) {
            // Si pas de plan et mode sans abonnement désactivé, bloquer
            if ($user->allow_without_subscription != 1) {
                return redirect()->route('admin.subscription.plans')
                    ->with('error', 'Vous devez souscrire à un abonnement pour continuer.');
            }
            return $next($request);
        }

        // Récupérer le plan de l'utilisateur
        $plan = PricingPlan::find($user->plan_id);

        if (!$plan) {
            return $next($request);
        }

        // Vérifier les limites selon le type
        if ($limitType === 'products') {
            $currentCount = Item::where('vendor_id', $user->id)->count();

            if ($plan->products_limit != -1 && $currentCount >= $plan->products_limit) {
                return redirect()->back()->with('error',
                    "Limite de produits atteinte ({$plan->products_limit}). Veuillez mettre à niveau votre abonnement.");
            }
        }

        if ($limitType === 'categories') {
            $currentCount = Category::where('vendor_id', $user->id)->count();

            if ($plan->categories_limit != -1 && $currentCount >= $plan->categories_limit) {
                return redirect()->back()->with('error',
                    "Limite de catégories atteinte ({$plan->categories_limit}). Veuillez mettre à niveau votre abonnement.");
            }
        }

        if ($limitType === 'custom_domain') {
            if (!$plan->custom_domain) {
                return redirect()->back()->with('error',
                    'Le domaine personnalisé n\'est pas disponible dans votre plan actuel. Veuillez mettre à niveau votre abonnement.');
            }
        }

        if ($limitType === 'analytics') {
            if (!$plan->analytics) {
                return redirect()->back()->with('error',
                    'Les analytics ne sont pas disponibles dans votre plan actuel. Veuillez mettre à niveau votre abonnement.');
            }
        }

        return $next($request);
    }
}
