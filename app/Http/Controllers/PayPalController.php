<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\PayPalTransaction;
use App\Models\PayPalSubscription;
use App\Services\PayPalService;
use Exception;

class PayPalController extends Controller
{
    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Obtenir les paramètres PayPal
     */
    public function getSettings()
    {
        try {
            $settings = [
                'client_id' => config('services.paypal.client_id'),
                'mode' => config('services.paypal.mode', 'sandbox'),
                'webhook_url' => config('services.paypal.webhook_url'),
                'currency' => config('services.paypal.currency', 'EUR'),
                'enabled' => config('services.paypal.enabled', false),
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);

        } catch (Exception $e) {
            Log::error('PayPal Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres'
            ], 500);
        }
    }

    /**
     * Mettre à jour les paramètres PayPal
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'mode' => 'required|in:sandbox,live',
                'webhook_url' => 'nullable|url',
                'currency' => 'required|string|size:3',
                'enabled' => 'boolean',
            ]);

            // Mettre à jour la configuration
            config(['services.paypal.client_id' => $validated['client_id']]);
            config(['services.paypal.client_secret' => $validated['client_secret']]);
            config(['services.paypal.mode' => $validated['mode']]);
            config(['services.paypal.webhook_url' => $validated['webhook_url']]);
            config(['services.paypal.currency' => $validated['currency']]);
            config(['services.paypal.enabled' => $validated['enabled'] ?? false]);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres PayPal mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('PayPal Settings Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres'
            ], 500);
        }
    }

    /**
     * Tester la connexion PayPal
     */
    public function testConnection()
    {
        try {
            $result = $this->paypalService->testConnection();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);

        } catch (Exception $e) {
            Log::error('PayPal Connection Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test de connexion'
            ], 500);
        }
    }

    /**
     * Créer un paiement PayPal Express
     */
    public function createExpressPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'return_url' => 'required|url',
                'cancel_url' => 'required|url',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            $paymentData = [
                'amount' => $order->total_amount,
                'currency' => config('services.paypal.currency', 'EUR'),
                'description' => 'Commande #' . $order->id,
                'return_url' => $validated['return_url'],
                'cancel_url' => $validated['cancel_url'],
            ];

            $result = $this->paypalService->createExpressPayment($paymentData);

            if ($result['success']) {
                // Enregistrer la transaction
                PayPalTransaction::create([
                    'order_id' => $order->id,
                    'paypal_payment_id' => $result['payment_id'],
                    'type' => 'express_checkout',
                    'status' => 'created',
                    'amount' => $order->total_amount,
                    'currency' => $paymentData['currency'],
                ]);
            }

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('PayPal Express Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du paiement'
            ], 500);
        }
    }

    /**
     * Exécuter un paiement PayPal
     */
    public function executePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'paymentId' => 'required|string',
                'PayerID' => 'required|string',
            ]);

            $result = $this->paypalService->executePayment(
                $validated['paymentId'],
                $validated['PayerID']
            );

            if ($result['success']) {
                // Mettre à jour la transaction
                $transaction = PayPalTransaction::where('paypal_payment_id', $validated['paymentId'])->first();
                if ($transaction) {
                    $transaction->update([
                        'status' => 'completed',
                        'payer_id' => $validated['PayerID'],
                        'transaction_details' => $result['details'],
                    ]);

                    // Mettre à jour la commande
                    $transaction->order->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'paypal',
                        'paid_at' => now(),
                    ]);
                }
            }

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('PayPal Execute Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'exécution du paiement'
            ], 500);
        }
    }

    /**
     * Annuler un paiement PayPal
     */
    public function cancelPayment(Request $request)
    {
        try {
            $paymentId = $request->get('paymentId');

            if ($paymentId) {
                $transaction = PayPalTransaction::where('paypal_payment_id', $paymentId)->first();
                if ($transaction) {
                    $transaction->update(['status' => 'cancelled']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Paiement annulé'
            ]);

        } catch (Exception $e) {
            Log::error('PayPal Cancel Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation'
            ], 500);
        }
    }

    /**
     * Capturer un paiement autorisé
     */
    public function capturePayment(Request $request, $paymentId)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|size:3',
            ]);

            $result = $this->paypalService->capturePayment(
                $paymentId,
                $validated['amount'],
                $validated['currency']
            );

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('PayPal Capture Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la capture du paiement'
            ], 500);
        }
    }

    /**
     * Créer un paiement direct (carte de crédit)
     */
    public function createDirectPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'credit_card' => 'required|array',
                'credit_card.type' => 'required|in:visa,mastercard,amex,discover',
                'credit_card.number' => 'required|string',
                'credit_card.expire_month' => 'required|integer|between:1,12',
                'credit_card.expire_year' => 'required|integer|min:' . date('Y'),
                'credit_card.cvv2' => 'required|string|size:3',
                'credit_card.first_name' => 'required|string',
                'credit_card.last_name' => 'required|string',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            $result = $this->paypalService->createDirectPayment($order, $validated['credit_card']);

            if ($result['success']) {
                PayPalTransaction::create([
                    'order_id' => $order->id,
                    'paypal_payment_id' => $result['payment_id'],
                    'type' => 'direct_credit_card',
                    'status' => $result['status'],
                    'amount' => $order->total_amount,
                    'currency' => config('services.paypal.currency', 'EUR'),
                ]);
            }

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('PayPal Direct Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du paiement direct'
            ], 500);
        }
    }

    /**
     * Créer un remboursement
     */
    public function createRefund(Request $request, $transactionId)
    {
        try {
            $validated = $request->validate([
                'amount' => 'nullable|numeric|min:0.01',
                'reason' => 'nullable|string|max:255',
            ]);

            $transaction = PayPalTransaction::where('paypal_payment_id', $transactionId)->firstOrFail();

            $result = $this->paypalService->createRefund(
                $transactionId,
                $validated['amount'] ?? null,
                $validated['reason'] ?? 'Remboursement demandé'
            );

            if ($result['success']) {
                $transaction->update([
                    'refund_id' => $result['refund_id'],
                    'refund_amount' => $result['refund_amount'],
                    'refund_status' => 'pending',
                ]);
            }

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('PayPal Refund Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du remboursement'
            ], 500);
        }
    }

    /**
     * Gérer les webhooks PayPal
     */
    public function handleWebhook(Request $request)
    {
        try {
            $headers = $request->headers->all();
            $body = $request->getContent();

            // Vérifier la signature du webhook
            if (!$this->paypalService->verifyWebhookSignature($headers, $body)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $eventData = json_decode($body, true);

            Log::info('PayPal Webhook Received', ['event' => $eventData]);

            $this->processWebhookEvent($eventData);

            return response()->json(['status' => 'success']);

        } catch (Exception $e) {
            Log::error('PayPal Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Traiter un événement webhook
     */
    private function processWebhookEvent($eventData)
    {
        $eventType = $eventData['event_type'] ?? null;

        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                $this->handlePaymentCompleted($eventData);
                break;

            case 'PAYMENT.SALE.DENIED':
                $this->handlePaymentDenied($eventData);
                break;

            case 'PAYMENT.SALE.REFUNDED':
                $this->handlePaymentRefunded($eventData);
                break;

            case 'BILLING.SUBSCRIPTION.CREATED':
                $this->handleSubscriptionCreated($eventData);
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $this->handleSubscriptionCancelled($eventData);
                break;

            default:
                Log::info('Unhandled PayPal webhook event: ' . $eventType);
        }
    }

    /**
     * Traiter un paiement complété
     */
    private function handlePaymentCompleted($eventData)
    {
        $paymentId = $eventData['resource']['parent_payment'] ?? null;

        if ($paymentId) {
            $transaction = PayPalTransaction::where('paypal_payment_id', $paymentId)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'webhook_data' => $eventData,
                ]);

                $transaction->order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }
    }

    /**
     * Traiter un paiement refusé
     */
    private function handlePaymentDenied($eventData)
    {
        $paymentId = $eventData['resource']['parent_payment'] ?? null;

        if ($paymentId) {
            $transaction = PayPalTransaction::where('paypal_payment_id', $paymentId)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => 'denied',
                    'webhook_data' => $eventData,
                ]);

                $transaction->order->update([
                    'payment_status' => 'failed',
                ]);
            }
        }
    }

    /**
     * Traiter un remboursement
     */
    private function handlePaymentRefunded($eventData)
    {
        $saleId = $eventData['resource']['sale_id'] ?? null;

        if ($saleId) {
            $transaction = PayPalTransaction::where('paypal_payment_id', $saleId)->first();
            if ($transaction) {
                $transaction->update([
                    'refund_status' => 'completed',
                    'refund_amount' => $eventData['resource']['amount']['total'] ?? 0,
                    'webhook_data' => $eventData,
                ]);
            }
        }
    }

    /**
     * Obtenir les devises supportées
     */
    public function getSupportedCurrencies()
    {
        $currencies = [
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
            'GBP' => 'British Pound',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'JPY' => 'Japanese Yen',
        ];

        return response()->json([
            'success' => true,
            'currencies' => $currencies
        ]);
    }

    /**
     * Obtenir les pays supportés
     */
    public function getSupportedCountries()
    {
        $countries = [
            'FR' => 'France',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'IT' => 'Italy',
            'ES' => 'Spain',
        ];

        return response()->json([
            'success' => true,
            'countries' => $countries
        ]);
    }

    /**
     * Méthodes placeholder pour les autres fonctionnalités
     */
    public function listSubscriptions() { return response()->json(['success' => true, 'subscriptions' => []]); }
    public function createSubscription() { return response()->json(['success' => true]); }
    public function getSubscription($id) { return response()->json(['success' => true]); }
    public function activateSubscription($id) { return response()->json(['success' => true]); }
    public function suspendSubscription($id) { return response()->json(['success' => true]); }
    public function cancelSubscription($id) { return response()->json(['success' => true]); }
    public function listPlans() { return response()->json(['success' => true, 'plans' => []]); }
    public function createPlan() { return response()->json(['success' => true]); }
    public function getPlan($id) { return response()->json(['success' => true]); }
    public function updatePlan($id) { return response()->json(['success' => true]); }
    public function deactivatePlan($id) { return response()->json(['success' => true]); }
    public function getRefund($id) { return response()->json(['success' => true]); }
    public function getTransactionRefunds($id) { return response()->json(['success' => true]); }
    public function listDisputes() { return response()->json(['success' => true, 'disputes' => []]); }
    public function getDispute($id) { return response()->json(['success' => true]); }
    public function submitEvidence($id) { return response()->json(['success' => true]); }
    public function acceptDispute($id) { return response()->json(['success' => true]); }
    public function listTransactions() { return response()->json(['success' => true, 'transactions' => []]); }
    public function getTransaction($id) { return response()->json(['success' => true]); }
    public function getOrderTransactions($id) { return response()->json(['success' => true]); }
    public function listWebhookEvents() { return response()->json(['success' => true, 'events' => []]); }
    public function verifyWebhook($id) { return response()->json(['success' => true]); }
    public function getWebhookConfig() { return response()->json(['success' => true]); }
    public function updateWebhookConfig() { return response()->json(['success' => true]); }
    public function registerWebhook() { return response()->json(['success' => true]); }
    public function unregisterWebhook($id) { return response()->json(['success' => true]); }
    public function paymentsReport() { return response()->json(['success' => true]); }
    public function subscriptionsReport() { return response()->json(['success' => true]); }
    public function refundsReport() { return response()->json(['success' => true]); }
    public function disputesReport() { return response()->json(['success' => true]); }
    public function analyticsReport() { return response()->json(['success' => true]); }
    public function exportReport() { return response()->json(['success' => true]); }
    public function getCustomerPayments($id) { return response()->json(['success' => true]); }
    public function getCustomerSubscriptions($id) { return response()->json(['success' => true]); }
    public function vaultPaymentMethod($id) { return response()->json(['success' => true]); }
    public function deletePaymentMethod($customerId, $methodId) { return response()->json(['success' => true]); }
    public function createBillingAgreement() { return response()->json(['success' => true]); }
    public function executeBillingAgreement() { return response()->json(['success' => true]); }
    public function chargeAgreement($id) { return response()->json(['success' => true]); }
    public function cancelAgreement($id) { return response()->json(['success' => true]); }
    public function createMobileOrder() { return response()->json(['success' => true]); }
    public function captureMobileOrder($id) { return response()->json(['success' => true]); }
    public function getMobilePaymentMethods() { return response()->json(['success' => true]); }
    public function validateWebhookSignature() { return response()->json(['success' => true]); }
    public function verifyPaymentStatus() { return response()->json(['success' => true]); }
    public function ipnListener() { return response()->json(['success' => true]); }
    public function webhookReceiver() { return response()->json(['success' => true]); }
    public function paymentReturn($id) { return response()->json(['success' => true]); }
    public function paymentCancel($id) { return response()->json(['success' => true]); }
    public function createCheckoutOrder() { return response()->json(['success' => true]); }
    public function captureCheckoutOrder() { return response()->json(['success' => true]); }
    public function getOrderDetails($id) { return response()->json(['success' => true]); }
    public function validateOrder() { return response()->json(['success' => true]); }
}
