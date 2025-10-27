<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class FacebookAuthService
{
    protected $appId;
    protected $appSecret;
    protected $baseUrl = 'https://graph.facebook.com';
    protected $version = 'v18.0';

    public function __construct()
    {
        $this->appId = config('services.facebook.client_id');
        $this->appSecret = config('services.facebook.client_secret');
    }

    /**
     * Obtenir le profil utilisateur depuis un token d'accès
     */
    public function getUserProfile($accessToken)
    {
        try {
            $response = Http::get($this->baseUrl . '/' . $this->version . '/me', [
                'access_token' => $accessToken,
                'fields' => 'id,name,email,picture.type(large),first_name,last_name,locale,timezone'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook Profile Error: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Facebook Profile Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Valider un token d'accès Facebook
     */
    public function validateToken($accessToken)
    {
        try {
            $response = Http::get($this->baseUrl . '/debug_token', [
                'input_token' => $accessToken,
                'access_token' => $this->getAppAccessToken()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && $data['data']['is_valid']) {
                    return [
                        'valid' => true,
                        'expires_at' => $data['data']['expires_at'] ?? null,
                        'scopes' => $data['data']['scopes'] ?? [],
                        'user_id' => $data['data']['user_id'] ?? null,
                        'app_id' => $data['data']['app_id'] ?? null,
                    ];
                }
            }

            return ['valid' => false];

        } catch (Exception $e) {
            Log::error('Facebook Token Validation Error: ' . $e->getMessage());
            return ['valid' => false];
        }
    }

    /**
     * Obtenir un token d'accès d'application
     */
    protected function getAppAccessToken()
    {
        return $this->appId . '|' . $this->appSecret;
    }

    /**
     * Échanger un code d'autorisation contre un token d'accès
     */
    public function exchangeCodeForToken($code, $redirectUri)
    {
        try {
            $response = Http::get($this->baseUrl . '/' . $this->version . '/oauth/access_token', [
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook Token Exchange Error: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Facebook Token Exchange Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Étendre la durée de vie d'un token d'accès
     */
    public function extendToken($shortLivedToken)
    {
        try {
            $response = Http::get($this->baseUrl . '/' . $this->version . '/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret,
                'fb_exchange_token' => $shortLivedToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook Token Extension Error: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Facebook Token Extension Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Révoquer un token d'accès
     */
    public function revokeToken($accessToken)
    {
        try {
            $response = Http::delete($this->baseUrl . '/' . $this->version . '/me/permissions', [
                'access_token' => $accessToken
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Facebook Revoke Token Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les amis Facebook
     */
    public function getFriends($accessToken, $limit = 100)
    {
        try {
            $response = Http::get($this->baseUrl . '/' . $this->version . '/me/friends', [
                'access_token' => $accessToken,
                'limit' => min($limit, 5000),
                'fields' => 'id,name,picture'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }

            return [];

        } catch (Exception $e) {
            Log::error('Facebook Friends Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Publier sur le mur Facebook
     */
    public function postToWall($accessToken, $message, $link = null)
    {
        try {
            $postData = [
                'access_token' => $accessToken,
                'message' => $message,
            ];

            if ($link) {
                $postData['link'] = $link;
            }

            $response = Http::post($this->baseUrl . '/' . $this->version . '/me/feed', $postData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('Facebook Post Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir l'URL d'autorisation Facebook
     */
    public function getAuthorizationUrl($redirectUri, $scopes = ['email', 'public_profile'], $state = null)
    {
        $params = [
            'client_id' => $this->appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
            'state' => $state ?? uniqid(),
        ];

        return 'https://www.facebook.com/' . $this->version . '/dialog/oauth?' . http_build_query($params);
    }

    /**
     * Obtenir les pages gérées par l'utilisateur
     */
    public function getUserPages($accessToken)
    {
        try {
            $response = Http::get($this->baseUrl . '/' . $this->version . '/me/accounts', [
                'access_token' => $accessToken,
                'fields' => 'id,name,access_token,category,picture'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }

            return [];

        } catch (Exception $e) {
            Log::error('Facebook Pages Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Tester la connexion avec Facebook
     */
    public function testConnection()
    {
        try {
            // Test avec l'API App Info
            $response = Http::get($this->baseUrl . '/' . $this->version . '/' . $this->appId, [
                'access_token' => $this->getAppAccessToken(),
                'fields' => 'id,name,category'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connexion Facebook réussie',
                    'data' => [
                        'app_id' => $this->appId,
                        'app_name' => $data['name'] ?? 'App Name',
                        'category' => $data['category'] ?? 'Unknown',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur de connexion Facebook'
            ];

        } catch (Exception $e) {
            Log::error('Facebook Connection Test Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les permissions disponibles
     */
    public function getAvailablePermissions()
    {
        return [
            'public_profile' => 'Profil public',
            'email' => 'Adresse email',
            'user_friends' => 'Liste d\'amis',
            'user_posts' => 'Publications utilisateur',
            'user_photos' => 'Photos utilisateur',
            'user_videos' => 'Vidéos utilisateur',
            'user_location' => 'Localisation',
            'user_hometown' => 'Ville natale',
            'user_birthday' => 'Date de naissance',
            'user_likes' => 'Pages aimées',
            'manage_pages' => 'Gestion des pages',
            'publish_pages' => 'Publication sur les pages',
            'read_insights' => 'Lecture des statistiques',
        ];
    }

    /**
     * Obtenir les permissions d'un token
     */
    public function getTokenPermissions($accessToken)
    {
        try {
            $response = Http::get($this->baseUrl . '/' . $this->version . '/me/permissions', [
                'access_token' => $accessToken
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $permissions = [];

                foreach ($data['data'] ?? [] as $permission) {
                    if ($permission['status'] === 'granted') {
                        $permissions[] = $permission['permission'];
                    }
                }

                return $permissions;
            }

            return [];

        } catch (Exception $e) {
            Log::error('Facebook Token Permissions Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir les statistiques d'utilisation
     */
    public function getUsageStats()
    {
        $cacheKey = 'facebook_auth_stats';

        return Cache::remember($cacheKey, 3600, function () {
            // Statistiques simulées - à remplacer par de vraies données
            return [
                'total_users' => 0,
                'active_users' => 0,
                'tokens_issued' => 0,
                'posts_shared' => 0,
                'errors_count' => 0,
            ];
        });
    }

    /**
     * Webhook de sécurité Facebook
     */
    public function verifyWebhook($hubMode, $hubToken, $hubChallenge)
    {
        $verifyToken = config('services.facebook.webhook_verify_token', 'my_verify_token');

        if ($hubMode === 'subscribe' && $hubToken === $verifyToken) {
            return $hubChallenge;
        }

        return false;
    }

    /**
     * Traiter un webhook Facebook
     */
    public function processWebhook($data)
    {
        try {
            Log::info('Facebook Webhook Received', ['data' => $data]);

            // Traiter les événements selon le type
            foreach ($data['entry'] ?? [] as $entry) {
                $this->processWebhookEntry($entry);
            }

            return ['status' => 'success'];

        } catch (Exception $e) {
            Log::error('Facebook Webhook Processing Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Traiter une entrée de webhook
     */
    protected function processWebhookEntry($entry)
    {
        $changes = $entry['changes'] ?? [];

        foreach ($changes as $change) {
            $field = $change['field'] ?? null;
            $value = $change['value'] ?? null;

            Log::info('Facebook Webhook Change', [
                'field' => $field,
                'value' => $value
            ]);

            // Traiter selon le type de changement
            switch ($field) {
                case 'feed':
                    $this->processFeedChange($value);
                    break;

                case 'permissions':
                    $this->processPermissionChange($value);
                    break;

                default:
                    Log::info('Unhandled Facebook webhook field: ' . $field);
            }
        }
    }

    /**
     * Traiter un changement de feed
     */
    protected function processFeedChange($value)
    {
        // Logique pour traiter les changements de feed
        Log::info('Facebook Feed Change', ['value' => $value]);
    }

    /**
     * Traiter un changement de permissions
     */
    protected function processPermissionChange($value)
    {
        // Logique pour traiter les changements de permissions
        Log::info('Facebook Permission Change', ['value' => $value]);
    }
}
