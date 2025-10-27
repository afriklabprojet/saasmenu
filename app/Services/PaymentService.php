<?php

namespace App\Services;

use App\Models\Order;
use App\Models\POSSession;
use App\Models\POSTerminal;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * Traiter un paiement en espèces
     */
    public function processCashPayment($orderId, $amountReceived, $sessionId)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);

            if ($amountReceived < $order->total_amount) {
                throw new Exception('Montant insuffisant reçu');
            }

            $change = $amountReceived - $order->total_amount;

            // Mettre à jour la commande
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'amount_received' => $amountReceived,
                'change_amount' => $change,
                'paid_at' => now(),
            ]);

            // Mettre à jour la session POS
            $this->updateSessionCash($sessionId, $order->total_amount, $change);

            DB::commit();

            return [
                'success' => true,
                'order_id' => $order->id,
                'total_amount' => $order->total_amount,
                'amount_received' => $amountReceived,
                'change_amount' => $change,
                'payment_method' => 'cash'
            ];

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Traiter un paiement par carte
     */
    public function processCardPayment($orderId, $cardType = 'credit', $transactionRef = null)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);

            // Simulation du traitement de carte
            // Dans un vrai système, ici on intégrerait avec un processeur de paiement
            $transactionId = $transactionRef ?? 'TXN_' . time() . '_' . $orderId;

            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $cardType . '_card',
                'transaction_reference' => $transactionId,
                'paid_at' => now(),
            ]);

            // Mettre à jour les statistiques de la session
            $this->updateSessionCard($order->pos_session_id, $order->total_amount);

            DB::commit();

            return [
                'success' => true,
                'order_id' => $order->id,
                'total_amount' => $order->total_amount,
                'payment_method' => $cardType . '_card',
                'transaction_id' => $transactionId
            ];

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('Erreur lors du paiement par carte: ' . $e->getMessage());
        }
    }

    /**
     * Traiter un paiement mixte (espèces + carte)
     */
    public function processMixedPayment($orderId, $cashAmount, $cardAmount, $sessionId)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);

            if (($cashAmount + $cardAmount) < $order->total_amount) {
                throw new Exception('Montant total insuffisant');
            }

            $change = ($cashAmount + $cardAmount) - $order->total_amount;

            $order->update([
                'payment_status' => 'paid',
                'payment_method' => 'mixed',
                'cash_amount' => $cashAmount,
                'card_amount' => $cardAmount,
                'change_amount' => $change,
                'paid_at' => now(),
            ]);

            // Mettre à jour la session
            $this->updateSessionCash($sessionId, $cashAmount, $change);
            $this->updateSessionCard($sessionId, $cardAmount);

            DB::commit();

            return [
                'success' => true,
                'order_id' => $order->id,
                'total_amount' => $order->total_amount,
                'cash_amount' => $cashAmount,
                'card_amount' => $cardAmount,
                'change_amount' => $change,
                'payment_method' => 'mixed'
            ];

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('Erreur lors du paiement mixte: ' . $e->getMessage());
        }
    }

    /**
     * Traiter un remboursement
     */
    public function processRefund($orderId, $refundAmount, $reason = '', $sessionId)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);

            if ($refundAmount > $order->total_amount) {
                throw new Exception('Montant de remboursement supérieur au total de la commande');
            }

            if ($order->payment_status === 'refunded') {
                throw new Exception('Cette commande a déjà été remboursée');
            }

            // Créer l'enregistrement de remboursement
            $refund = $order->refunds()->create([
                'amount' => $refundAmount,
                'reason' => $reason,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'refund_method' => $order->payment_method,
            ]);

            // Mettre à jour la commande
            if ($refundAmount >= $order->total_amount) {
                $order->update(['payment_status' => 'refunded']);
            } else {
                $order->update(['payment_status' => 'partially_refunded']);
            }

            // Ajuster les statistiques de la session
            $this->adjustSessionForRefund($sessionId, $refundAmount, $order->payment_method);

            DB::commit();

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'order_id' => $order->id,
                'refund_amount' => $refundAmount,
                'refund_method' => $order->payment_method
            ];

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('Erreur lors du remboursement: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getAvailablePaymentMethods($terminalId)
    {
        $terminal = POSTerminal::find($terminalId);

        $methods = [
            'cash' => [
                'name' => 'Espèces',
                'enabled' => true,
                'icon' => 'fa-money-bill'
            ]
        ];

        if ($terminal && $terminal->card_reader_enabled) {
            $methods['credit_card'] = [
                'name' => 'Carte de crédit',
                'enabled' => true,
                'icon' => 'fa-credit-card'
            ];

            $methods['debit_card'] = [
                'name' => 'Carte de débit',
                'enabled' => true,
                'icon' => 'fa-credit-card'
            ];
        }

        if ($terminal && $terminal->contactless_enabled) {
            $methods['contactless'] = [
                'name' => 'Sans contact',
                'enabled' => true,
                'icon' => 'fa-wifi'
            ];
        }

        return $methods;
    }

    /**
     * Valider un montant de paiement
     */
    public function validatePaymentAmount($amount, $orderTotal)
    {
        if (!is_numeric($amount) || $amount <= 0) {
            return ['valid' => false, 'message' => 'Montant invalide'];
        }

        if ($amount < $orderTotal) {
            return ['valid' => false, 'message' => 'Montant insuffisant'];
        }

        return ['valid' => true];
    }

    /**
     * Calculer la monnaie à rendre
     */
    public function calculateChange($amountReceived, $orderTotal)
    {
        $change = $amountReceived - $orderTotal;

        return [
            'change_amount' => round($change, 2),
            'breakdown' => $this->getChangeBreakdown($change)
        ];
    }

    /**
     * Décomposition de la monnaie
     */
    private function getChangeBreakdown($amount)
    {
        $denominations = [
            50 => 0, 20 => 0, 10 => 0, 5 => 0,
            2 => 0, 1 => 0, 0.50 => 0, 0.20 => 0,
            0.10 => 0, 0.05 => 0, 0.02 => 0, 0.01 => 0
        ];

        $remaining = round($amount, 2);

        foreach ($denominations as $value => $count) {
            if ($remaining >= $value) {
                $denominations[$value] = floor($remaining / $value);
                $remaining = round($remaining - ($denominations[$value] * $value), 2);
            }
        }

        return array_filter($denominations);
    }

    /**
     * Mettre à jour les espèces de la session
     */
    private function updateSessionCash($sessionId, $cashReceived, $changeGiven = 0)
    {
        $session = POSSession::find($sessionId);
        if ($session) {
            $session->increment('cash_in_drawer', $cashReceived - $changeGiven);
            $session->increment('cash_sales', $cashReceived - $changeGiven);
        }
    }

    /**
     * Mettre à jour les paiements par carte de la session
     */
    private function updateSessionCard($sessionId, $cardAmount)
    {
        $session = POSSession::find($sessionId);
        if ($session) {
            $session->increment('card_sales', $cardAmount);
        }
    }

    /**
     * Ajuster la session pour un remboursement
     */
    private function adjustSessionForRefund($sessionId, $refundAmount, $paymentMethod)
    {
        $session = POSSession::find($sessionId);
        if ($session) {
            $session->decrement('total_sales', $refundAmount);

            if (strpos($paymentMethod, 'cash') !== false) {
                $session->decrement('cash_sales', $refundAmount);
                $session->decrement('cash_in_drawer', $refundAmount);
            } else {
                $session->decrement('card_sales', $refundAmount);
            }
        }
    }

    /**
     * Générer un reçu de paiement
     */
    public function generatePaymentReceipt($orderId)
    {
        $order = Order::with(['items.menuItem', 'restaurant'])
            ->findOrFail($orderId);

        return [
            'order_id' => $order->id,
            'restaurant' => $order->restaurant->name ?? 'RestroSaaS',
            'date' => $order->created_at->format('d/m/Y H:i'),
            'items' => $order->items->map(function($item) {
                return [
                    'name' => $item->menuItem->name ?? 'Article',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total_price
                ];
            }),
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'amount_received' => $order->amount_received,
            'change_amount' => $order->change_amount,
        ];
    }

    /**
     * Traiter un paiement POS générique
     */
    public function processPOSPayment($paymentData)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($paymentData['order_id']);
            $paymentMethod = $paymentData['payment_method'];

            // Traitement selon la méthode de paiement
            switch ($paymentMethod) {
                case 'cash':
                    $result = $this->processCashPayment(
                        $order->id,
                        $paymentData['amount_received'],
                        $paymentData['session_id']
                    );
                    break;

                case 'card':
                case 'contactless':
                    $result = $this->processGenericCardPayment($order->id, $paymentData);
                    break;

                default:
                    $result = $this->processOtherPayment($order->id, $paymentData);
                    break;
            }

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement du paiement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traiter un paiement par carte générique
     */
    private function processGenericCardPayment($orderId, $paymentData)
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentData['payment_method'],
            'amount_received' => $order->total_amount,
            'change_amount' => 0,
            'paid_at' => now(),
            'transaction_id' => $paymentData['transaction_id'] ?? 'TXN_' . time(),
        ]);

        return [
            'success' => true,
            'order_id' => $order->id,
            'total_amount' => $order->total_amount,
            'payment_method' => $paymentData['payment_method'],
            'transaction_id' => $order->transaction_id,
        ];
    }

    /**
     * Traiter d'autres méthodes de paiement
     */
    private function processOtherPayment($orderId, $paymentData)
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentData['payment_method'],
            'amount_received' => $order->total_amount,
            'change_amount' => 0,
            'paid_at' => now(),
        ]);

        return [
            'success' => true,
            'order_id' => $order->id,
            'total_amount' => $order->total_amount,
            'payment_method' => $paymentData['payment_method'],
        ];
    }
}
