<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class PayPalService
{
    protected $clientId;
    protected $clientSecret;
    protected $mode;
    protected $baseUrl;
    protected $accessToken;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->mode = config('services.paypal.mode', 'sandbox');

        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Obtenir le token d'accès PayPal
     */
    protected function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $cacheKey = 'paypal_access_token_' . $this->mode;

        return Cache::remember($cacheKey, 3300, function () { // 55 minutes
            try {
                $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                    ->asForm()
                    ->post($this->baseUrl . '/v1/oauth2/token', [
                        'grant_type' => 'client_credentials'
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->accessToken = $data['access_token'];
                    return $this->accessToken;
                }

                throw new Exception('Failed to get PayPal access token: ' . $response->body());

            } catch (Exception $e) {
                Log::error('PayPal Access Token Error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Tester la connexion PayPal
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();

            if ($token) {
                return [
                    'success' => true,
                    'message' => 'Connexion PayPal réussie',
                    'data' => [
                        'mode' => $this->mode,
                        'client_id' => substr($this->clientId, 0, 10) . '...',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Échec de la connexion PayPal'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Créer un paiement PayPal Express
     */
    public function createExpressPayment($paymentData)
    {
        try {
            $token = $this->getAccessToken();

            $payment = [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'paypal'
                ],
                'redirect_urls' => [
                    'return_url' => $paymentData['return_url'],
                    'cancel_url' => $paymentData['cancel_url']
                ],
                'transactions' => [
                    [
                        'amount' => [
                            'total' => number_format($paymentData['amount'], 2, '.', ''),
                            'currency' => $paymentData['currency']
                        ],
                        'description' => $paymentData['description'] ?? 'Paiement RestroSaaS'
                    ]
                ]
            ];

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/v1/payments/payment', $payment);

            if ($response->successful()) {
                $data = $response->json();

                // Trouver l'URL d'approbation
                $approvalUrl = null;
                foreach ($data['links'] as $link) {
                    if ($link['rel'] === 'approval_url') {
                        $approvalUrl = $link['href'];
                        break;
                    }
                }

                return [
                    'success' => true,
                    'payment_id' => $data['id'],
                    'approval_url' => $approvalUrl,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la création du paiement',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('PayPal Express Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Exécuter un paiement PayPal
     */
    public function executePayment($paymentId, $payerId)
    {
        try {
            $token = $this->getAccessToken();

            $execution = [
                'payer_id' => $payerId
            ];

            $response = Http::withToken($token)
                ->post($this->baseUrl . "/v1/payments/payment/{$paymentId}/execute", $execution);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'payment_id' => $data['id'],
                    'status' => $data['state'],
                    'details' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'exécution du paiement',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('PayPal Execute Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer un paiement direct par carte de crédit
     */
    public function createDirectPayment($order, $creditCard)
    {
        try {
            $token = $this->getAccessToken();

            $payment = [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'credit_card',
                    'funding_instruments' => [
                        [
                            'credit_card' => [
                                'type' => $creditCard['type'],
                                'number' => $creditCard['number'],
                                'expire_month' => $creditCard['expire_month'],
                                'expire_year' => $creditCard['expire_year'],
                                'cvv2' => $creditCard['cvv2'],
                                'first_name' => $creditCard['first_name'],
                                'last_name' => $creditCard['last_name'],
                                'billing_address' => [
                                    'line1' => '123 Main St',
                                    'city' => 'Paris',
                                    'state' => 'IDF',
                                    'postal_code' => '75001',
                                    'country_code' => 'FR'
                                ]
                            ]
                        ]
                    ]
                ],
                'transactions' => [
                    [
                        'amount' => [
                            'total' => number_format($order->total_amount, 2, '.', ''),
                            'currency' => config('services.paypal.currency', 'EUR')
                        ],
                        'description' => 'Commande #' . $order->id
                    ]
                ]
            ];

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/v1/payments/payment', $payment);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'payment_id' => $data['id'],
                    'status' => $data['state'],
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors du paiement direct',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('PayPal Direct Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Capturer un paiement autorisé
     */
    public function capturePayment($paymentId, $amount, $currency)
    {
        try {
            $token = $this->getAccessToken();

            $capture = [
                'amount' => [
                    'currency' => $currency,
                    'total' => number_format($amount, 2, '.', '')
                ],
                'is_final_capture' => true
            ];

            $response = Http::withToken($token)
                ->post($this->baseUrl . "/v1/payments/authorization/{$paymentId}/capture", $capture);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'capture_id' => $data['id'],
                    'status' => $data['state'],
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la capture',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('PayPal Capture Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer un remboursement
     */
    public function createRefund($saleId, $amount = null, $reason = '')
    {
        try {
            $token = $this->getAccessToken();

            $refund = [
                'reason' => $reason
            ];

            if ($amount) {
                $refund['amount'] = [
                    'total' => number_format($amount, 2, '.', ''),
                    'currency' => config('services.paypal.currency', 'EUR')
                ];
            }

            $response = Http::withToken($token)
                ->post($this->baseUrl . "/v1/payments/sale/{$saleId}/refund", $refund);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'refund_id' => $data['id'],
                    'refund_amount' => $data['amount']['total'],
                    'status' => $data['state'],
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors du remboursement',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('PayPal Refund Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier la signature d'un webhook
     */
    public function verifyWebhookSignature($headers, $body)
    {
        try {
            $webhookId = config('services.paypal.webhook_id');

            if (!$webhookId) {
                return true; // Pas de vérification si pas configuré
            }

            $token = $this->getAccessToken();

            $verification = [
                'auth_algo' => $headers['paypal-auth-algo'][0] ?? '',
                'cert_id' => $headers['paypal-cert-id'][0] ?? '',
                'transmission_id' => $headers['paypal-transmission-id'][0] ?? '',
                'transmission_sig' => $headers['paypal-transmission-sig'][0] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'][0] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($body, true)
            ];

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/v1/notifications/verify-webhook-signature', $verification);

            if ($response->successful()) {
                $data = $response->json();
                return $data['verification_status'] === 'SUCCESS';
            }

            return false;

        } catch (Exception $e) {
            Log::error('PayPal Webhook Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer un plan d'abonnement
     */
    public function createSubscriptionPlan($planData)
    {
        try {
            $token = $this->getAccessToken();

            $plan = [
                'name' => $planData['name'],
                'description' => $planData['description'],
                'type' => 'INFINITE',
                'payment_definitions' => [
                    [
                        'name' => 'Regular Payments',
                        'type' => 'REGULAR',
                        'frequency_interval' => $planData['frequency_interval'],
                        'frequency' => strtoupper($planData['frequency']),
                        'cycles' => '0',
                        'amount' => [
                            'value' => number_format($planData['amount'], 2, '.', ''),
                            'currency' => $planData['currency']
                        ]
                    ]
                ],
                'merchant_preferences' => [
                    'setup_fee' => [
                        'value' => '0',
                        'currency' => $planData['currency']
                    ],
                    'cancel_url' => $planData['cancel_url'],
                    'return_url' => $planData['return_url'],
                    'auto_bill_amount' => 'YES',
                    'initial_fail_amount_action' => 'CONTINUE',
                    'max_fail_attempts' => '3'
                ]
            ];

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/v1/payments/billing-plans', $plan);

            if ($response->successful()) {
                $data = $response->json();

                // Activer le plan
                $this->activateBillingPlan($data['id']);

                return [
                    'success' => true,
                    'plan_id' => $data['id'],
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la création du plan',
                'error' => $response->json()
            ];

        } catch (Exception $e) {
            Log::error('PayPal Create Plan Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Activer un plan de facturation
     */
    protected function activateBillingPlan($planId)
    {
        try {
            $token = $this->getAccessToken();

            $patch = [
                [
                    'op' => 'replace',
                    'path' => '/',
                    'value' => [
                        'state' => 'ACTIVE'
                    ]
                ]
            ];

            Http::withToken($token)
                ->patch($this->baseUrl . "/v1/payments/billing-plans/{$planId}", $patch);

        } catch (Exception $e) {
            Log::error('PayPal Activate Plan Error: ' . $e->getMessage());
        }
    }
}
