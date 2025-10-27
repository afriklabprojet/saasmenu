<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class GoogleAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl = 'https://www.googleapis.com';

    public function __construct()
    {
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
    }

    /**
     * Obtenir le profil utilisateur depuis un token d'accès
     */
    public function getUserProfile($accessToken)
    {
        try {
            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . '/oauth2/v2/userinfo');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Profile Error: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Google Profile Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Valider un token d'accès Google
     */
    public function validateToken($accessToken)
    {
        try {
            $response = Http::get($this->baseUrl . '/oauth2/v1/tokeninfo', [
                'access_token' => $accessToken
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Vérifier que le token appartient à notre application
                if (isset($data['audience']) && $data['audience'] === $this->clientId) {
                    return [
                        'valid' => true,
                        'expires_in' => $data['expires_in'] ?? 0,
                        'scope' => $data['scope'] ?? '',
                        'user_id' => $data['user_id'] ?? null,
                    ];
                }
            }

            return ['valid' => false];

        } catch (Exception $e) {
            Log::error('Google Token Validation Error: ' . $e->getMessage());
            return ['valid' => false];
        }
    }

    /**
     * Échanger un code d'autorisation contre un token d'accès
     */
    public function exchangeCodeForToken($code, $redirectUri)
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/oauth2/v4/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Token Exchange Error: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Google Token Exchange Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiser un token d'accès
     */
    public function refreshToken($refreshToken)
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/oauth2/v4/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Refresh Token Error: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Google Refresh Token Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Révoquer un token d'accès
     */
    public function revokeToken($accessToken)
    {
        try {
            $response = Http::post($this->baseUrl . '/oauth2/v1/revoke', [
                'token' => $accessToken
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Google Revoke Token Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les informations de contact Google
     */
    public function getContacts($accessToken, $maxResults = 100)
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://people.googleapis.com/v1/people/me/connections', [
                    'personFields' => 'names,emailAddresses,phoneNumbers',
                    'pageSize' => min($maxResults, 1000),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['connections'] ?? [];
            }

            return [];

        } catch (Exception $e) {
            Log::error('Google Contacts Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir l'URL d'autorisation Google
     */
    public function getAuthorizationUrl($redirectUri, $scopes = ['openid', 'profile', 'email'], $state = null)
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Tester la connexion avec Google
     */
    public function testConnection()
    {
        try {
            // Test avec une requête simple vers l'API Google
            $response = Http::get('https://www.googleapis.com/.well-known/openid_configuration');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connexion Google réussie',
                    'data' => [
                        'client_id' => substr($this->clientId, 0, 10) . '...',
                        'endpoints' => $response->json(),
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur de connexion Google'
            ];

        } catch (Exception $e) {
            Log::error('Google Connection Test Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les scopes disponibles
     */
    public function getAvailableScopes()
    {
        return [
            'openid' => 'Identification OpenID',
            'profile' => 'Informations de profil',
            'email' => 'Adresse email',
            'https://www.googleapis.com/auth/contacts.readonly' => 'Lecture des contacts',
            'https://www.googleapis.com/auth/calendar.readonly' => 'Lecture du calendrier',
            'https://www.googleapis.com/auth/drive.readonly' => 'Lecture de Google Drive',
            'https://www.googleapis.com/auth/gmail.readonly' => 'Lecture de Gmail',
        ];
    }

    /**
     * Vérifier les permissions d'un token
     */
    public function getTokenPermissions($accessToken)
    {
        try {
            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . '/oauth2/v1/tokeninfo');

            if ($response->successful()) {
                $data = $response->json();
                $scope = $data['scope'] ?? '';

                return [
                    'scopes' => explode(' ', $scope),
                    'expires_in' => $data['expires_in'] ?? 0,
                    'user_id' => $data['user_id'] ?? null,
                ];
            }

            return null;

        } catch (Exception $e) {
            Log::error('Google Token Permissions Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Partager du contenu sur Google+
     */
    public function shareContent($accessToken, $content)
    {
        // Note: Google+ a été fermé, cette méthode est un placeholder
        // pour d'autres services de partage Google futurs

        Log::info('Google Share Content: ' . json_encode($content));

        return [
            'success' => true,
            'message' => 'Contenu partagé (simulation)',
            'data' => $content
        ];
    }

    /**
     * Obtenir les statistiques d'utilisation
     */
    public function getUsageStats()
    {
        $cacheKey = 'google_auth_stats';

        return Cache::remember($cacheKey, 3600, function () {
            // Statistiques simulées - à remplacer par de vraies données
            return [
                'total_users' => 0,
                'active_users' => 0,
                'tokens_issued' => 0,
                'tokens_refreshed' => 0,
                'errors_count' => 0,
            ];
        });
    }
}
