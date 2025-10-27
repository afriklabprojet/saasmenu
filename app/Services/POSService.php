<?php

namespace App\Services;

use App\Models\POSCart;
use App\Models\POSSession;
use App\Models\POSTerminal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class POSService
{
    /**
     * Ajouter un article au panier
     */
    public function addToCart($terminalId, $userId, $sessionId, $menuItemId, $quantity = 1, $modifiers = [], $specialInstructions = null)
    {
        try {
            // Vérifier que la session est active
            $session = POSSession::where('id', $sessionId)
                ->where('terminal_id', $terminalId)
                ->where('status', 'active')
                ->first();

            if (!$session) {
                throw new Exception('Session POS non trouvée ou inactive');
            }

            // Vérifier si l'article existe déjà dans le panier
            $existingItem = POSCart::where('terminal_id', $terminalId)
                ->where('session_id', $sessionId)
                ->where('menu_item_id', $menuItemId)
                ->where('modifiers', json_encode($modifiers))
                ->first();

            if ($existingItem) {
                // Augmenter la quantité
                $existingItem->increment('quantity', $quantity);
                $existingItem->updateTotalPrice();
                return $existingItem;
            }

            // Créer un nouvel article dans le panier
            $cartItem = POSCart::create([
                'terminal_id' => $terminalId,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'menu_item_id' => $menuItemId,
                'quantity' => $quantity,
                'modifiers' => $modifiers,
                'special_instructions' => $specialInstructions,
            ]);

            return $cartItem;

        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'ajout au panier: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un article du panier
     */
    public function removeFromCart($cartItemId, $terminalId, $sessionId)
    {
        try {
            $cartItem = POSCart::where('id', $cartItemId)
                ->where('terminal_id', $terminalId)
                ->where('session_id', $sessionId)
                ->first();

            if (!$cartItem) {
                throw new Exception('Article non trouvé dans le panier');
            }

            $cartItem->delete();
            return true;

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour la quantité d'un article
     */
    public function updateCartItemQuantity($cartItemId, $quantity, $terminalId, $sessionId)
    {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($cartItemId, $terminalId, $sessionId);
            }

            $cartItem = POSCart::where('id', $cartItemId)
                ->where('terminal_id', $terminalId)
                ->where('session_id', $sessionId)
                ->first();

            if (!$cartItem) {
                throw new Exception('Article non trouvé dans le panier');
            }

            $cartItem->update(['quantity' => $quantity]);
            $cartItem->updateTotalPrice();

            return $cartItem;

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir le contenu du panier
     */
    public function getCartContents($terminalId, $sessionId)
    {
        $cartItems = POSCart::where('terminal_id', $terminalId)
            ->where('session_id', $sessionId)
            ->with('menuItem')
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = $this->calculateCartSummary($cartItems);

        return [
            'items' => $cartItems->map(function($item) {
                return $item->toDisplayArray();
            }),
            'summary' => $summary
        ];
    }

    /**
     * Calculer le résumé du panier
     */
    public function calculateCartSummary($cartItems)
    {
        $subtotal = $cartItems->sum('total_price');
        $tax_rate = config('pos.tax_rate', 0.20);
        $tax_amount = $subtotal * $tax_rate;
        $total = $subtotal + $tax_amount;

        return [
            'item_count' => $cartItems->sum('quantity'),
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $tax_rate,
            'tax_amount' => round($tax_amount, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Vider le panier
     */
    public function clearCart($terminalId, $sessionId)
    {
        try {
            POSCart::where('terminal_id', $terminalId)
                ->where('session_id', $sessionId)
                ->delete();

            return true;

        } catch (Exception $e) {
            throw new Exception('Erreur lors du vidage du panier: ' . $e->getMessage());
        }
    }

    /**
     * Créer une commande à partir du panier
     */
    public function createOrderFromCart($terminalId, $sessionId, $customerData = [], $paymentMethod = 'cash', $discountAmount = 0)
    {
        DB::beginTransaction();

        try {
            // Récupérer les articles du panier
            $cartItems = POSCart::where('terminal_id', $terminalId)
                ->where('session_id', $sessionId)
                ->with('menuItem')
                ->get();

            if ($cartItems->isEmpty()) {
                throw new Exception('Le panier est vide');
            }

            // Calculer le total
            $summary = $this->calculateCartSummary($cartItems);
            $finalTotal = $summary['total'] - $discountAmount;

            // Créer la commande
            $order = Order::create([
                'restaurant_id' => $this->getTerminalRestaurantId($terminalId),
                'pos_terminal_id' => $terminalId,
                'pos_session_id' => $sessionId,
                'customer_name' => $customerData['name'] ?? 'Client POS',
                'customer_phone' => $customerData['phone'] ?? null,
                'order_type' => 'pos',
                'payment_method' => $paymentMethod,
                'subtotal' => $summary['subtotal'],
                'tax_amount' => $summary['tax_amount'],
                'discount_amount' => $discountAmount,
                'total_amount' => $finalTotal,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'created_at' => now(),
            ]);

            // Ajouter les articles de commande
            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                    'modifiers' => $cartItem->modifiers,
                    'special_instructions' => $cartItem->special_instructions,
                ]);
            }

            // Vider le panier
            $this->clearCart($terminalId, $sessionId);

            // Mettre à jour les statistiques de la session
            $this->updateSessionStats($sessionId, $finalTotal, $paymentMethod);

            DB::commit();

            return $order;

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception('Erreur lors de la création de la commande: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les statistiques de session
     */
    private function updateSessionStats($sessionId, $amount, $paymentMethod)
    {
        $session = POSSession::find($sessionId);
        if ($session) {
            $session->increment('total_sales', $amount);
            $session->increment('total_orders');

            if ($paymentMethod === 'cash') {
                $session->increment('cash_sales', $amount);
            } else {
                $session->increment('card_sales', $amount);
            }
        }
    }

    /**
     * Obtenir l'ID du restaurant pour un terminal
     */
    private function getTerminalRestaurantId($terminalId)
    {
        $terminal = POSTerminal::find($terminalId);
        return $terminal ? $terminal->restaurant_id : 1;
    }

    /**
     * Rechercher des articles dans le menu
     */
    public function searchMenuItems($query, $restaurantId, $limit = 20)
    {
        // Cette méthode devra être implémentée selon la structure des menus
        // Pour l'instant, nous retournons un placeholder
        return [];
    }

    /**
     * Obtenir les articles populaires
     */
    public function getPopularItems($terminalId, $limit = 10)
    {
        // Logique pour obtenir les articles les plus vendus
        // Basé sur les statistiques de commandes POS
        return [];
    }

    /**
     * Appliquer une remise
     */
    public function applyDiscount($terminalId, $sessionId, $discountType, $discountValue)
    {
        try {
            $cartSummary = $this->getCartContents($terminalId, $sessionId)['summary'];

            $discountAmount = 0;

            if ($discountType === 'percentage') {
                $discountAmount = ($cartSummary['subtotal'] * $discountValue) / 100;
            } elseif ($discountType === 'fixed') {
                $discountAmount = $discountValue;
            }

            // Limiter la remise au montant du panier
            $discountAmount = min($discountAmount, $cartSummary['total']);

            return [
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => round($discountAmount, 2),
                'new_total' => round($cartSummary['total'] - $discountAmount, 2)
            ];

        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'application de la remise: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les paramètres POS
     */
    public function getSettings()
    {
        return [
            'tax_rate' => config('pos.tax_rate', 20),
            'service_charge' => config('pos.service_charge', 0),
            'receipt_header' => config('pos.receipt_header', ''),
            'receipt_footer' => config('pos.receipt_footer', ''),
            'auto_print_receipt' => config('pos.auto_print_receipt', true),
            'allow_discount' => config('pos.allow_discount', true),
            'require_customer_info' => config('pos.require_customer_info', false),
            'default_payment_method' => config('pos.default_payment_method', 'cash'),
            'currency_symbol' => config('pos.currency_symbol', '€'),
            'currency_position' => config('pos.currency_position', 'after'),
        ];
    }

    /**
     * Mettre à jour les paramètres POS
     */
    public function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            config(['pos.' . $key => $value]);
        }

        // Sauvegarder dans la base de données ou fichier de configuration
        return true;
    }

    /**
     * Synchroniser un terminal
     */
    public function syncTerminal($terminal)
    {
        try {
            // Synchroniser les données du menu
            $terminal->update([
                'last_sync_at' => now(),
                'sync_status' => 'completed'
            ]);

            return true;
        } catch (Exception $e) {
            $terminal->update(['sync_status' => 'failed']);
            throw new Exception('Erreur lors de la synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Tester une imprimante
     */
    public function testPrinter($printerId)
    {
        try {
            // Implémentation du test d'impression
            // Cette méthode dépend du driver d'imprimante utilisé

            $testContent = "=== TEST D'IMPRESSION ===\n";
            $testContent .= "Date: " . now()->format('d/m/Y H:i:s') . "\n";
            $testContent .= "Imprimante ID: " . $printerId . "\n";
            $testContent .= "=========================\n";

            // Ici vous pourriez utiliser une bibliothèque d'impression
            // comme mike42/escpos-php pour les imprimantes ESC/POS

            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors du test d\'impression: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir ou créer une session POS
     */
    public function getOrCreateSession($terminalId)
    {
        // Chercher une session active
        $session = POSSession::where('terminal_id', $terminalId)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            // Créer une nouvelle session
            $session = POSSession::create([
                'terminal_id' => $terminalId,
                'user_id' => auth()->id(),
                'started_at' => now(),
                'status' => 'active',
                'initial_cash' => 0,
                'settings' => json_encode([])
            ]);
        }

        return $session;
    }

    /**
     * Obtenir ou créer un panier
     */
    public function getOrCreateCart($terminalId, $userId)
    {
        $session = POSSession::where('terminal_id', $terminalId)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            $session = POSSession::create([
                'terminal_id' => $terminalId,
                'user_id' => $userId,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        return POSCart::where('terminal_id', $terminalId)
            ->where('session_id', $session->id)
            ->with(['menuItem'])
            ->get();
    }

    /**
     * Obtenir les catégories du menu
     */
    public function getMenuCategories($restaurantId)
    {
        return \App\Models\Category::where('user_id', $restaurantId)
            ->where('is_active', 1)
            ->with(['items' => function($query) {
                $query->where('is_available', 1);
            }])
            ->get();
    }

    /**
     * Obtenir les méthodes de paiement
     */
    public function getPaymentMethods($restaurantId)
    {
        $defaultMethods = config('pos.accepted_payment_methods', []);

        // Ici vous pourriez récupérer des méthodes personnalisées par restaurant
        return $defaultMethods;
    }

    /**
     * Obtenir les tables actives
     */
    public function getActiveTables($restaurantId)
    {
        return \App\Models\Table::where('user_id', $restaurantId)
            ->where('is_active', 1)
            ->get();
    }

    /**
     * Obtenir le résumé du panier
     */
    public function getCartSummary($terminalId)
    {
        $cartItems = POSCart::where('terminal_id', $terminalId)
            ->with(['menuItem'])
            ->get();

        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->price;
        });

        $taxRate = config('pos.tax_rate', 20) / 100;
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
            'items_count' => $cartItems->sum('quantity')
        ];
    }

    /**
     * Mettre à jour l'inventaire après commande
     */
    public function updateInventoryFromOrder($order)
    {
        // Implémentation future pour la gestion d'inventaire
        return true;
    }
}
