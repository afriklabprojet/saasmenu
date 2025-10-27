<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Restaurant;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ValidateAddonPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $addon): ResponseAlias
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Authentication required',
                'message' => 'Vous devez être connecté pour accéder à cette ressource.'
            ], 401);
        }

        // Vérifier si l'utilisateur a accès au restaurant
        $restaurantId = $this->getRestaurantId($request);

        if (!$restaurantId) {
            return response()->json([
                'error' => 'Restaurant not specified',
                'message' => 'L\'ID du restaurant doit être spécifié.'
            ], 400);
        }

        $restaurant = Restaurant::find($restaurantId);

        if (!$restaurant) {
            return response()->json([
                'error' => 'Restaurant not found',
                'message' => 'Restaurant introuvable.'
            ], 404);
        }

        // Vérifier les permissions utilisateur pour ce restaurant
        if (!$this->hasRestaurantAccess($user, $restaurant)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Vous n\'avez pas accès à ce restaurant.'
            ], 403);
        }

        // Vérifier que l'addon est activé pour ce restaurant
        if (!$this->isAddonEnabled($restaurant, $addon)) {
            return response()->json([
                'error' => 'Addon not enabled',
                'message' => "L'addon '{$addon}' n'est pas activé pour ce restaurant."
            ], 403);
        }

        // Vérifier les permissions spécifiques à l'addon
        if (!$this->hasAddonPermission($user, $restaurant, $addon, $request)) {
            return response()->json([
                'error' => 'Permission denied',
                'message' => "Vous n'avez pas les permissions nécessaires pour utiliser '{$addon}'."
            ], 403);
        }

        // Ajouter les informations au request pour utilisation ultérieure
        $request->merge([
            'restaurant' => $restaurant,
            'addon' => $addon,
            'user_role' => $this->getUserRole($user, $restaurant)
        ]);

        return $next($request);
    }

    /**
     * Récupérer l'ID du restaurant depuis la requête
     */
    private function getRestaurantId(Request $request): ?int
    {
        // Essayer plusieurs sources pour l'ID restaurant
        return $request->route('restaurant_id')
            ?? $request->route('restaurantId')
            ?? $request->input('restaurant_id')
            ?? $request->header('X-Restaurant-ID')
            ?? session('current_restaurant_id');
    }

    /**
     * Vérifier si l'utilisateur a accès au restaurant
     */
    private function hasRestaurantAccess($user, Restaurant $restaurant): bool
    {
        // Propriétaire du restaurant
        if ($restaurant->user_id === $user->id) {
            return true;
        }

        // Super admin
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Membre de l'équipe restaurant
        if ($user->restaurants()->where('restaurant_id', $restaurant->id)->exists()) {
            return true;
        }

        // Utilisateur avec permission explicite
        return $user->hasPermissionTo("access_restaurant_{$restaurant->id}");
    }

    /**
     * Vérifier si l'addon est activé pour le restaurant
     */
    private function isAddonEnabled(Restaurant $restaurant, string $addon): bool
    {
        // Vérifier dans la configuration des addons du restaurant
        $enabledAddons = $restaurant->settings['enabled_addons'] ?? [];

        if (is_array($enabledAddons) && in_array($addon, $enabledAddons)) {
            return true;
        }

        // Vérifier dans la table des addons si elle existe
        if (method_exists($restaurant, 'addons')) {
            return $restaurant->addons()->where('name', $addon)->where('is_enabled', true)->exists();
        }

        // Par défaut, certains addons de base sont toujours activés
        $defaultAddons = ['api', 'tableqr', 'pos'];
        return in_array($addon, $defaultAddons);
    }

    /**
     * Vérifier les permissions spécifiques à l'addon
     */
    private function hasAddonPermission($user, Restaurant $restaurant, string $addon, Request $request): bool
    {
        $userRole = $this->getUserRole($user, $restaurant);
        $action = $this->getActionFromRequest($request);

        // Permissions par addon et rôle
        $permissions = $this->getAddonPermissions();

        if (!isset($permissions[$addon])) {
            return false; // Addon non reconnu
        }

        $addonPermissions = $permissions[$addon];

        // Vérifier les permissions par rôle
        if (isset($addonPermissions[$userRole])) {
            $allowedActions = $addonPermissions[$userRole];

            if ($allowedActions === true || (is_array($allowedActions) && in_array($action, $allowedActions))) {
                return true;
            }
        }

        // Vérifier les permissions explicites
        $permissionName = "addon_{$addon}_{$action}";
        return $user->hasPermissionTo($permissionName);
    }

    /**
     * Récupérer le rôle de l'utilisateur pour ce restaurant
     */
    private function getUserRole($user, Restaurant $restaurant): string
    {
        // Propriétaire
        if ($restaurant->user_id === $user->id) {
            return 'owner';
        }

        // Super admin
        if ($user->hasRole('super_admin')) {
            return 'super_admin';
        }

        // Rôle spécifique au restaurant
        $restaurantUser = $user->restaurants()->where('restaurant_id', $restaurant->id)->first();
        if ($restaurantUser && isset($restaurantUser->pivot->role)) {
            return $restaurantUser->pivot->role;
        }

        // Rôle global par défaut
        return $user->getRoleNames()->first() ?? 'customer';
    }

    /**
     * Déterminer l'action depuis la requête
     */
    private function getActionFromRequest(Request $request): string
    {
        $method = $request->method();

        return match($method) {
            'GET' => 'read',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'read'
        };
    }

    /**
     * Configuration des permissions par addon
     */
    private function getAddonPermissions(): array
    {
        return [
            'api' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => ['read', 'create', 'update'],
                'employee' => ['read'],
                'customer' => ['read']
            ],
            'tableqr' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => ['read', 'create', 'update'],
                'employee' => ['read', 'update'],
                'customer' => ['read']
            ],
            'loyalty' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => ['read', 'create', 'update'],
                'employee' => ['read'],
                'customer' => ['read']
            ],
            'pos' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => true,
                'employee' => ['read', 'create', 'update'],
                'cashier' => ['read', 'create', 'update']
            ],
            'paypal' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => ['read'],
                'employee' => false,
                'customer' => ['create'] // Pour effectuer des paiements
            ],
            'social_login' => [
                'super_admin' => true,
                'owner' => ['read'],
                'customer' => ['create'] // Pour se connecter
            ],
            'firebase' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => ['read', 'create'],
                'employee' => false
            ],
            'import_export' => [
                'super_admin' => true,
                'owner' => true,
                'manager' => ['read', 'create'],
                'employee' => false
            ]
        ];
    }
}
