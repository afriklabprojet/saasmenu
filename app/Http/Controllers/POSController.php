<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\POSSession;
use App\Models\POSTerminal;
use App\Models\POSCart;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Customer;
use App\Models\LoyaltyMember;
use App\Models\Table;
use App\Services\POSService;
use App\Services\LoyaltyService;
use App\Services\PaymentService;
use Carbon\Carbon;

class POSController extends Controller
{
    protected $posService;
    protected $loyaltyService;
    protected $paymentService;

    public function __construct(
        POSService $posService,
        LoyaltyService $loyaltyService,
        PaymentService $paymentService
    ) {
        $this->posService = $posService;
        $this->loyaltyService = $loyaltyService;
        $this->paymentService = $paymentService;
    }

    /**
     * Interface principale POS
     */
    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        // Vérifier session POS active
        $activeSession = POSSession::where('restaurant_id', $restaurant->id)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        $terminals = POSTerminal::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->get();

        return view('pos.index', compact('restaurant', 'activeSession', 'terminals'));
    }

    /**
     * Interface terminal POS spécifique
     */
    public function terminal(Request $request, $terminal_id = null)
    {
        $restaurant = auth()->user()->restaurant;

        $terminal = POSTerminal::where('restaurant_id', $restaurant->id)
            ->where('id', $terminal_id)
            ->firstOrFail();

        // Vérifier que le terminal n'est pas utilisé par quelqu'un d'autre
        if ($terminal->current_user_id && $terminal->current_user_id !== auth()->id()) {
            return redirect()->route('pos.index')
                ->withErrors(['terminal' => 'Ce terminal est déjà utilisé par un autre utilisateur.']);
        }

        // Marquer le terminal comme utilisé
        $terminal->update([
            'current_user_id' => auth()->id(),
            'last_activity' => now(),
        ]);

        // Récupérer ou créer le panier pour ce terminal
        $cart = $this->posService->getOrCreateCart($terminal->id, auth()->id());

        // Données pour l'interface POS
        $data = [
            'terminal' => $terminal,
            'cart' => $cart,
            'categories' => $this->posService->getMenuCategories($restaurant->id),
            'payment_methods' => $this->posService->getPaymentMethods($restaurant->id),
            'tables' => $this->posService->getActiveTables($restaurant->id),
        ];

        return view('pos.terminal', $data);
    }

    /**
     * Ajouter un article au panier POS
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'modifiers' => 'nullable|array',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        try {
            $restaurant = auth()->user()->restaurant;

            // Obtenir ou créer une session POS
            $session = POSSession::where('terminal_id', $request->terminal_id)
                ->where('status', 'active')
                ->first();

            if (!$session) {
                $session = POSSession::create([
                    'terminal_id' => $request->terminal_id,
                    'user_id' => auth()->id(),
                    'status' => 'active',
                    'started_at' => now(),
                ]);
            }

            // Vérifier que l'article appartient au restaurant
            $menuItem = MenuItem::where('id', $request->menu_item_id)
                ->where('restaurant_id', $restaurant->id)
                ->where('status', 'active')
                ->first();

            if (!$menuItem) {
                return response()->json(['error' => 'Article non trouvé'], 404);
            }

            // Vérifier le stock si activé
            if ($menuItem->track_inventory && $menuItem->stock_quantity < $request->quantity) {
                return response()->json([
                    'error' => 'Stock insuffisant',
                    'available_quantity' => $menuItem->stock_quantity
                ], 400);
            }

            $cartItem = $this->posService->addToCart(
                $request->terminal_id,
                auth()->id(),
                $session->id,
                $request->menu_item_id,
                $request->quantity,
                $request->modifiers ?? [],
                $request->special_instructions
            );

            return response()->json([
                'success' => true,
                'cart_item' => $cartItem,
                'cart_summary' => $this->posService->getCartSummary($request->terminal_id),
                'message' => 'Article ajouté au panier'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur ajout panier POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'ajout au panier'], 500);
        }
    }

    /**
     * Mettre à jour un article du panier
     */
    public function updateCartItem(Request $request, $item_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = POSCart::where('id', $item_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $cartItem->update(['quantity' => $request->quantity]);

            return response()->json([
                'success' => true,
                'cart_item' => $cartItem->fresh(),
                'cart_summary' => $this->posService->getCartSummary($cartItem->terminal_id),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour panier POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    /**
     * Supprimer un article du panier
     */
    public function removeFromCart($item_id)
    {
        try {
            $cartItem = POSCart::where('id', $item_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $terminalId = $cartItem->terminal_id;
            $cartItem->delete();

            return response()->json([
                'success' => true,
                'cart_summary' => $this->posService->getCartSummary($terminalId),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression panier POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression'], 500);
        }
    }

    /**
     * Vider le panier
     */
    public function clearCart(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
        ]);

        try {
            POSCart::where('terminal_id', $request->terminal_id)
                ->where('user_id', auth()->id())
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Panier vidé'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur vidage panier POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du vidage du panier'], 500);
        }
    }

    /**
     * Résumé du panier
     */
    public function getCartSummary(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
        ]);

        $summary = $this->posService->getCartSummary($request->terminal_id);
        return response()->json($summary);
    }

    /**
     * Créer une commande depuis POS
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
            'customer_id' => 'nullable|exists:customers,id',
            'table_id' => 'nullable|exists:tables,id',
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $restaurant = auth()->user()->restaurant;
            $terminal = POSTerminal::findOrFail($request->terminal_id);

            // Récupérer ou créer une session POS
            $session = $this->posService->getOrCreateSession($request->terminal_id);

            // Récupérer les articles du panier
            $cartItems = POSCart::where('terminal_id', $request->terminal_id)
                ->where('user_id', auth()->id())
                ->with('menuItem')
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Panier vide'], 400);
            }

            // Créer la commande
            $customerData = [
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'table_id' => $request->table_id,
                'order_type' => $request->order_type,
                'special_instructions' => $request->special_instructions,
            ];

            $order = $this->posService->createOrderFromCart(
                $request->terminal_id,
                $session->id ?? null,
                $customerData,
                $request->payment_method ?? 'cash',
                $request->discount_amount ?? 0
            );

            // Vider le panier
            $cartItems->each->delete();

            // Mettre à jour l'inventaire si nécessaire
            $this->posService->updateInventoryFromOrder($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                ],
                'message' => 'Commande créée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création de la commande'], 500);
        }
    }

    /**
     * Commandes en cours
     */
    public function getCurrentOrders(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->where('order_source', 'pos')
            ->with(['items.menuItem', 'table', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'order_type' => $order->order_type,
                    'total_amount' => $order->total_amount,
                    'customer_name' => $order->customer_name,
                    'table_number' => $order->table?->table_number,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->format('H:i'),
                    'created_at_human' => $order->created_at->diffForHumans(),
                ];
            });

        return response()->json(['orders' => $orders]);
    }

    /**
     * Traiter un paiement POS
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'amount_tendered' => 'required|numeric|min:0',
            'tip_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $restaurant = auth()->user()->restaurant;
            $order = Order::where('id', $request->order_id)
                ->where('restaurant_id', $restaurant->id)
                ->firstOrFail();

            $totalAmount = $order->total_amount + ($request->tip_amount ?? 0);

            if ($request->amount_tendered < $totalAmount) {
                return response()->json(['error' => 'Montant insuffisant'], 400);
            }

            // Traiter le paiement
            $payment = $this->paymentService->processPOSPayment([
                'order' => $order,
                'payment_method' => $request->payment_method,
                'amount' => $totalAmount,
                'amount_tendered' => $request->amount_tendered,
                'tip_amount' => $request->tip_amount,
            ]);

            // Calculer la monnaie
            $change = $request->amount_tendered - $totalAmount;

            // Mettre à jour la commande
            $order->update([
                'status' => 'paid',
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            // Traiter les points de fidélité si applicable
            $this->processLoyaltyPoints($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment' => $payment,
                'change' => $change,
                'order' => $order->fresh(),
                'message' => 'Paiement traité avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur paiement POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du traitement du paiement'], 500);
        }
    }

    /**
     * Rechercher des clients
     */
    public function searchCustomers(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $restaurant = auth()->user()->restaurant;
        $query = $request->get('query');

        $customers = Customer::where('restaurant_id', $restaurant->id)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'total_orders' => $customer->orders()->count(),
                    'loyalty_member' => $customer->loyaltyMember ? true : false,
                ];
            });

        return response()->json(['customers' => $customers]);
    }

    /**
     * Obtenir les informations de fidélité d'un client
     */
    public function getCustomerLoyalty(Request $request, $customer_id)
    {
        $restaurant = auth()->user()->restaurant;

        $customer = Customer::where('id', $customer_id)
            ->where('restaurant_id', $restaurant->id)
            ->firstOrFail();

        $loyaltyMember = LoyaltyMember::where('restaurant_id', $restaurant->id)
            ->where('email', $customer->email)
            ->first();

        if (!$loyaltyMember) {
            return response()->json([
                'has_loyalty' => false,
                'message' => 'Client non membre du programme de fidélité'
            ]);
        }

        // Suggestions de récompenses
        $availableRewards = $this->loyaltyService->suggestRewardsForAmount(
            $loyaltyMember,
            $request->get('order_amount', 0)
        );

        return response()->json([
            'has_loyalty' => true,
            'member' => [
                'name' => $loyaltyMember->name,
                'member_code' => $loyaltyMember->member_code,
                'points_balance' => $loyaltyMember->points_balance,
                'tier' => $loyaltyMember->tier?->name,
            ],
            'available_rewards' => $availableRewards->take(5),
        ]);
    }

    /**
     * Rechercher des produits
     */
    public function searchProducts(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $restaurant = auth()->user()->restaurant;
        $query = $request->get('query');

        $products = MenuItem::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('barcode', $query);
            })
            ->limit(20)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'category' => $item->category?->name,
                    'image_url' => $item->image_url,
                    'stock_quantity' => $item->stock_quantity,
                    'track_inventory' => $item->track_inventory,
                ];
            });

        return response()->json(['products' => $products]);
    }

    /**
     * Obtenir un produit par code-barres
     */
    public function getProductByBarcode(Request $request, $barcode)
    {
        $restaurant = auth()->user()->restaurant;

        $product = MenuItem::where('restaurant_id', $restaurant->id)
            ->where('barcode', $barcode)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Produit non trouvé'], 404);
        }

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category?->name,
                'image_url' => $product->image_url,
                'stock_quantity' => $product->stock_quantity,
                'track_inventory' => $product->track_inventory,
            ]
        ]);
    }

    /**
     * Ouvrir une session de caisse
     */
    public function openCashDrawer(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        try {
            $restaurant = auth()->user()->restaurant;

            // Vérifier qu'il n'y a pas déjà une session active
            $activeSession = POSSession::where('terminal_id', $request->terminal_id)
                ->where('status', 'active')
                ->first();

            if ($activeSession) {
                return response()->json(['error' => 'Une session est déjà active sur ce terminal'], 400);
            }

            $session = POSSession::create([
                'restaurant_id' => $restaurant->id,
                'terminal_id' => $request->terminal_id,
                'user_id' => auth()->id(),
                'opening_cash' => $request->opening_cash,
                'opened_at' => now(),
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'session' => $session,
                'message' => 'Session de caisse ouverte'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur ouverture caisse POS: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'ouverture de la caisse'], 500);
        }
    }

    /**
     * Traitement des points de fidélité
     */
    private function processLoyaltyPoints($order)
    {
        if (!$order->customer_email) return;

        $loyaltyMember = LoyaltyMember::where('restaurant_id', $order->restaurant_id)
            ->where('email', $order->customer_email)
            ->first();

        if ($loyaltyMember) {
            $points = $this->loyaltyService->calculatePointsFromAmount(
                $order->total_amount,
                $order->restaurant
            );

            if ($points > 0) {
                $this->loyaltyService->addPoints(
                    $loyaltyMember,
                    $points,
                    'order_purchase',
                    "Commande POS #{$order->order_number}"
                );
            }
        }
    }

    /**
     * API de santé pour vérifier le système
     */
    public function healthCheck()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    }
}
