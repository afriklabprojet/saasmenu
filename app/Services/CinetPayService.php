<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinetPayService
{
    private $config;

    public function __construct($vendor_id = 1)
    {
        $this->config = Payment::where('payment_type', '16')
            ->where('vendor_id', $vendor_id)
            ->where('is_available', 1)
            ->first();
    }

    /**
     * Initier un retrait Mobile Money via CinetPay
     */
    public function initiateWithdrawal($data)
    {
        if (!$this->config) {
            return ['success' => false, 'message' => 'CinetPay non configuré'];
        }

        try {
            $endpoint = $this->config->environment === 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/disbursement'
                : 'https://api-checkout.cinetpay.com/v2/disbursement';

            $payload = [
                'apikey' => $this->config->public_key,
                'site_id' => $this->config->secret_key,
                'amount' => (int) $data['amount'],
                'phone_number' => $this->formatPhoneNumber($data['phone_number']),
                'operator' => $this->getOperatorCode($data['operator']),
                'reference' => $data['reference'],
                'description' => 'Retrait E-menu - ' . $data['reference']
            ];

            $response = Http::timeout(30)->post($endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();

                if ($result['code'] === '201') {
                    return [
                        'success' => true,
                        'transaction_id' => $result['data']['transaction_id'],
                        'message' => 'Retrait initié avec succès'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erreur lors du retrait'
            ];

        } catch (\Exception $e) {
            Log::error('CinetPay Withdrawal Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur technique lors du retrait'
            ];
        }
    }

    /**
     * Vérifier le statut d'un retrait
     */
    public function checkWithdrawalStatus($transaction_id)
    {
        if (!$this->config) {
            return ['success' => false, 'message' => 'CinetPay non configuré'];
        }

        try {
            $endpoint = $this->config->environment === 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/disbursement/check'
                : 'https://api-checkout.cinetpay.com/v2/disbursement/check';

            $payload = [
                'apikey' => $this->config->public_key,
                'site_id' => $this->config->secret_key,
                'transaction_id' => $transaction_id
            ];

            $response = Http::timeout(30)->post($endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'status' => $result['data']['status'],
                    'message' => $result['message']
                ];
            }

            return [
                'success' => false,
                'message' => 'Impossible de vérifier le statut'
            ];

        } catch (\Exception $e) {
            Log::error('CinetPay Status Check Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ];
        }
    }

    /**
     * Obtenir le solde du compte CinetPay
     */
    public function getBalance()
    {
        if (!$this->config) {
            return ['success' => false, 'message' => 'CinetPay non configuré'];
        }

        try {
            $endpoint = $this->config->environment === 'sandbox'
                ? 'https://api-checkout.cinetpay.com/v2/balance'
                : 'https://api-checkout.cinetpay.com/v2/balance';

            $payload = [
                'apikey' => $this->config->public_key,
                'site_id' => $this->config->secret_key
            ];

            $response = Http::timeout(30)->post($endpoint, $payload);

            if ($response->successful()) {
                $result = $response->json();

                if ($result['code'] === '200') {
                    return [
                        'success' => true,
                        'balance' => $result['data']['balance'],
                        'currency' => $result['data']['currency']
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Impossible de récupérer le solde'
            ];

        } catch (\Exception $e) {
            Log::error('CinetPay Balance Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération du solde'
            ];
        }
    }

    private function formatPhoneNumber($phone)
    {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ajouter l'indicatif pays si nécessaire
        if (strlen($phone) === 8) {
            $phone = '225' . $phone;
        }

        return '+' . $phone;
    }

    private function getOperatorCode($operator)
    {
        $operators = [
            'orange' => 'ORANGE_MONEY_CI',
            'mtn' => 'MTN_MONEY_CI',
            'moov' => 'MOOV_MONEY_CI'
        ];

        return $operators[$operator] ?? 'ORANGE_MONEY_CI';
    }

    /**
     * Calculer les frais de retrait
     */
    public static function calculateWithdrawalFee($amount)
    {
        // Frais standard : 2% minimum 100 FCFA, maximum 2000 FCFA
        $fee = $amount * 0.02;
        $fee = max(100, $fee);  // Minimum 100 FCFA
        $fee = min(2000, $fee); // Maximum 2000 FCFA

        return $fee;
    }

    /**
     * Vérifier si un retrait est possible
     */
    public function canProcess($amount, $operator)
    {
        // Vérifications de base
        if ($amount < 1000) {
            return ['success' => false, 'message' => 'Montant minimum: 1000 FCFA'];
        }

        if ($amount > 1000000) {
            return ['success' => false, 'message' => 'Montant maximum: 1,000,000 FCFA'];
        }

        // Vérifier si l'opérateur est supporté
        $supportedOperators = ['orange', 'mtn', 'moov'];
        if (!in_array($operator, $supportedOperators)) {
            return ['success' => false, 'message' => 'Opérateur non supporté'];
        }

        return ['success' => true, 'message' => 'Retrait possible'];
    }
}
