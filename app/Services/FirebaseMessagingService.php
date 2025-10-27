<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;
use Google\Auth\AccessToken;
use Exception;

class FirebaseMessagingService
{
    protected $serverKey;
    protected $projectId;
    protected $credentials;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key');
        $this->projectId = config('services.firebase.project_id');
        $this->credentials = config('services.firebase.credentials');
        $this->baseUrl = 'https://fcm.googleapis.com';
    }

    /**
     * Tester la connexion Firebase
     */
    public function testConnection()
    {
        try {
            if (!$this->serverKey && !$this->credentials) {
                return [
                    'success' => false,
                    'message' => 'Aucune configuration Firebase trouvée',
                ];
            }

            // Test avec Server Key (Legacy)
            if ($this->serverKey) {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/fcm/send', [
                    'to' => 'test_token',
                    'notification' => [
                        'title' => 'Test Connection',
                        'body' => 'Firebase connection test'
                    ]
                ]);

                if ($response->status() === 400 && str_contains($response->body(), 'InvalidRegistration')) {
                    return [
                        'success' => true,
                        'message' => 'Connexion Firebase réussie (Server Key)',
                        'data' => ['method' => 'server_key']
                    ];
                }
            }

            // Test avec Service Account (HTTP v1)
            if ($this->credentials) {
                $accessToken = $this->getAccessToken();
                if ($accessToken) {
                    return [
                        'success' => true,
                        'message' => 'Connexion Firebase réussie (Service Account)',
                        'data' => ['method' => 'service_account']
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Impossible de se connecter à Firebase',
            ];

        } catch (Exception $e) {
            Log::error('Firebase Test Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envoyer une notification à plusieurs appareils
     */
    public function sendToDevices($deviceTokens, $notification)
    {
        try {
            if (empty($deviceTokens)) {
                return [
                    'success' => false,
                    'message' => 'Aucun token d\'appareil fourni'
                ];
            }

            $results = [];
            $chunks = array_chunk($deviceTokens, 1000); // FCM limite à 1000 tokens par requête

            foreach ($chunks as $chunk) {
                if ($this->credentials) {
                    $result = $this->sendWithHttpV1($chunk, $notification);
                } else {
                    $result = $this->sendWithServerKey($chunk, $notification);
                }
                $results[] = $result;
            }

            return [
                'success' => true,
                'message' => 'Notifications envoyées',
                'results' => $results
            ];

        } catch (Exception $e) {
            Log::error('Firebase Send To Devices Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification à un topic
     */
    public function sendToTopic($topic, $notification)
    {
        try {
            if ($this->credentials) {
                return $this->sendToTopicHttpV1($topic, $notification);
            } else {
                return $this->sendToTopicServerKey($topic, $notification);
            }
        } catch (Exception $e) {
            Log::error('Firebase Send To Topic Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi au topic: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification avec une condition
     */
    public function sendToCondition($condition, $notification)
    {
        try {
            if ($this->credentials) {
                return $this->sendToConditionHttpV1($condition, $notification);
            } else {
                return $this->sendToConditionServerKey($condition, $notification);
            }
        } catch (Exception $e) {
            Log::error('Firebase Send To Condition Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi avec condition: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Souscrire des appareils à un topic
     */
    public function subscribeToTopic($deviceTokens, $topic)
    {
        try {
            $tokens = is_array($deviceTokens) ? $deviceTokens : [$deviceTokens];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://iid.googleapis.com/iid/v1:batchAdd', [
                'to' => '/topics/' . $topic,
                'registration_tokens' => $tokens
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Souscription au topic réussie',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la souscription',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('Firebase Subscribe To Topic Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la souscription: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Désabonner des appareils d'un topic
     */
    public function unsubscribeFromTopic($deviceTokens, $topic)
    {
        try {
            $tokens = is_array($deviceTokens) ? $deviceTokens : [$deviceTokens];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://iid.googleapis.com/iid/v1:batchRemove', [
                'to' => '/topics/' . $topic,
                'registration_tokens' => $tokens
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Désabonnement du topic réussi',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors du désabonnement',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('Firebase Unsubscribe From Topic Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors du désabonnement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir un token d'accès pour l'API HTTP v1
     */
    protected function getAccessToken()
    {
        try {
            if (!$this->credentials) {
                return null;
            }

            $credentials = is_string($this->credentials) ? json_decode($this->credentials, true) : $this->credentials;

            $client = new GoogleClient();
            $client->setAuthConfig($credentials);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $accessToken = $client->fetchAccessTokenWithAssertion();

            return $accessToken['access_token'] ?? null;

        } catch (Exception $e) {
            Log::error('Firebase Get Access Token Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envoyer avec Server Key (Legacy)
     */
    protected function sendWithServerKey($deviceTokens, $notification)
    {
        $payload = [
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'image' => $notification['image'] ?? null,
            ],
            'data' => array_merge(
                $notification['data'] ?? [],
                ['action_url' => $notification['action_url'] ?? null]
            )
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/fcm/send', $payload);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Envoyer avec HTTP v1 API
     */
    protected function sendWithHttpV1($deviceTokens, $notification)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new Exception('Impossible d\'obtenir le token d\'accès');
        }

        $results = [];
        foreach ($deviceTokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $notification['title'],
                        'body' => $notification['body'],
                        'image' => $notification['image'] ?? null,
                    ],
                    'data' => array_merge(
                        $notification['data'] ?? [],
                        ['action_url' => $notification['action_url'] ?? null]
                    )
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/projects/' . $this->projectId . '/messages:send', $payload);

            $results[] = [
                'token' => $token,
                'success' => $response->successful(),
                'data' => $response->json(),
                'status_code' => $response->status()
            ];
        }

        return $results;
    }

    /**
     * Envoyer à un topic avec Server Key
     */
    protected function sendToTopicServerKey($topic, $notification)
    {
        $payload = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'image' => $notification['image'] ?? null,
            ],
            'data' => array_merge(
                $notification['data'] ?? [],
                ['action_url' => $notification['action_url'] ?? null]
            )
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/fcm/send', $payload);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Envoyer à un topic avec HTTP v1
     */
    protected function sendToTopicHttpV1($topic, $notification)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new Exception('Impossible d\'obtenir le token d\'accès');
        }

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                    'image' => $notification['image'] ?? null,
                ],
                'data' => array_merge(
                    $notification['data'] ?? [],
                    ['action_url' => $notification['action_url'] ?? null]
                )
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v1/projects/' . $this->projectId . '/messages:send', $payload);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Envoyer avec condition Server Key
     */
    protected function sendToConditionServerKey($condition, $notification)
    {
        $payload = [
            'condition' => $condition,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'image' => $notification['image'] ?? null,
            ],
            'data' => array_merge(
                $notification['data'] ?? [],
                ['action_url' => $notification['action_url'] ?? null]
            )
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/fcm/send', $payload);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Envoyer avec condition HTTP v1
     */
    protected function sendToConditionHttpV1($condition, $notification)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new Exception('Impossible d\'obtenir le token d\'accès');
        }

        $payload = [
            'message' => [
                'condition' => $condition,
                'notification' => [
                    'title' => $notification['title'],
                    'body' => $notification['body'],
                    'image' => $notification['image'] ?? null,
                ],
                'data' => array_merge(
                    $notification['data'] ?? [],
                    ['action_url' => $notification['action_url'] ?? null]
                )
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v1/projects/' . $this->projectId . '/messages:send', $payload);

        return [
            'success' => $response->successful(),
            'data' => $response->json(),
            'status_code' => $response->status()
        ];
    }

    /**
     * Valider un token d'appareil
     */
    public function validateDeviceToken($token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/fcm/send', [
                'to' => $token,
                'dry_run' => true,
                'notification' => [
                    'title' => 'Validation',
                    'body' => 'Token validation test'
                ]
            ]);

            $data = $response->json();
            return [
                'valid' => $response->successful() && ($data['success'] ?? 0) > 0,
                'data' => $data
            ];

        } catch (Exception $e) {
            Log::error('Firebase Validate Token Error: ' . $e->getMessage());
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
