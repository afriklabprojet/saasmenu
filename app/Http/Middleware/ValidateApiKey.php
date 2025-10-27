<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;
use App\Models\Restaurant;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $this->extractApiKey($request);

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Une clé API valide est requise pour accéder à cette ressource.'
            ], 401);
        }

        $keyModel = $this->validateApiKey($apiKey);

        if (!$keyModel) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'Clé API invalide ou expirée.'
            ], 401);
        }

        // Vérifier si la clé est active
        if (!$keyModel->is_active) {
            return response()->json([
                'error' => 'API key disabled',
                'message' => 'Cette clé API a été désactivée.'
            ], 403);
        }

        // Vérifier les permissions de la clé
        if (!$this->hasPermissionForRequest($keyModel, $request)) {
            return response()->json([
                'error' => 'Insufficient permissions',
                'message' => 'Cette clé API n\'a pas les permissions nécessaires pour cette action.'
            ], 403);
        }

        // Enregistrer l'usage de la clé
        $this->recordApiKeyUsage($keyModel, $request);

        // Ajouter les informations de la clé à la requête
        $request->merge([
            'api_key' => $keyModel,
            'api_restaurant_id' => $keyModel->restaurant_id,
            'api_user_id' => $keyModel->user_id
        ]);

        return $next($request);
    }

    /**
     * Extraire la clé API de la requête
     */
    private function extractApiKey(Request $request): ?string
    {
        // Essayer plusieurs méthodes d'extraction
        return $request->bearerToken()
            ?? $request->header('X-API-Key')
            ?? $request->header('Api-Key')
            ?? $request->query('api_key');
    }

    /**
     * Valider la clé API
     */
    private function validateApiKey(string $apiKey): ?ApiKey
    {
        // Hasher la clé pour la comparaison sécurisée
        $hashedKey = hash('sha256', $apiKey);

        return ApiKey::where('hashed_key', $hashedKey)
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /**
     * Vérifier si la clé a les permissions pour cette requête
     */
    private function hasPermissionForRequest(ApiKey $apiKey, Request $request): bool
    {
        $permissions = $apiKey->permissions ?? [];

        // Si aucune permission définie, accès complet (pour compatibilité)
        if (empty($permissions)) {
            return true;
        }

        $method = strtolower($request->method());
        $path = $request->path();

        // Vérifier les permissions par méthode HTTP
        $methodPermissions = $permissions['methods'] ?? [];
        if (!empty($methodPermissions) && !in_array($method, $methodPermissions)) {
            return false;
        }

        // Vérifier les permissions par endpoint
        $endpointPermissions = $permissions['endpoints'] ?? [];
        if (!empty($endpointPermissions)) {
            $hasEndpointAccess = false;
            foreach ($endpointPermissions as $allowedPath) {
                if ($this->pathMatches($path, $allowedPath)) {
                    $hasEndpointAccess = true;
                    break;
                }
            }
            if (!$hasEndpointAccess) {
                return false;
            }
        }

        // Vérifier les permissions par addon
        $addon = $request->get('addon');
        if ($addon) {
            $addonPermissions = $permissions['addons'] ?? [];
            if (!empty($addonPermissions) && !in_array($addon, $addonPermissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier si un chemin correspond à un pattern
     */
    private function pathMatches(string $path, string $pattern): bool
    {
        // Convertir le pattern en regex
        $regex = str_replace(
            ['*', '/'],
            ['.*', '\/'],
            $pattern
        );

        return preg_match("/^{$regex}$/", $path) === 1;
    }

    /**
     * Enregistrer l'usage de la clé API
     */
    private function recordApiKeyUsage(ApiKey $apiKey, Request $request): void
    {
        // Mettre à jour les statistiques d'usage
        $apiKey->increment('usage_count');
        $apiKey->update(['last_used_at' => now()]);

        // Optionnel : enregistrer les détails d'usage dans une table séparée
        // ApiKeyUsage::create([...]);
    }
}
