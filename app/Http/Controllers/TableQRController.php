<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\TableNotification;
use App\Services\QRCodeService;
use Carbon\Carbon;

class TableQRController extends Controller
{
    protected $qrService;

    public function __construct(QRCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Affiche le menu après scan du QR code de la table
     */
    public function showMenu($restaurant_slug, $table_code)
    {
        try {
            $restaurant = Restaurant::where('slug', $restaurant_slug)
                ->where('status', 'active')
                ->first();

            if (!$restaurant) {
                return view('tableqr.error', [
                    'message' => 'Restaurant non trouvé ou inactif'
                ]);
            }

            $table = Table::where('restaurant_id', $restaurant->id)
                ->where('table_code', $table_code)
                ->where('status', 'active')
                ->first();

            if (!$table) {
                return view('tableqr.error', [
                    'message' => 'Table non trouvée ou inactive'
                ]);
            }

            // Récupérer le menu du restaurant
            $menuItems = MenuItem::where('restaurant_id', $restaurant->id)
                ->where('status', 'active')
                ->with(['category', 'images'])
                ->orderBy('category_id')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('category.name');

            // ✨ Enregistrer le scan du QR code
            $this->qrService->recordScan($table, request());

            // Marquer la table comme occupée
            $table->update(['last_accessed' => now()]);

            return view('tableqr.menu', compact(
                'restaurant',
                'table',
                'menuItems'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur TableQR showMenu: ' . $e->getMessage());
            return view('tableqr.error', [
                'message' => 'Erreur lors du chargement du menu'
            ]);
        }
    }

    /**
     * Récupère les informations de la table
     */
    public function getTableInfo($restaurant_slug, $table_code)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('table_code', $table_code)
            ->first();

        if (!$table) {
            return response()->json(['error' => 'Table non trouvée'], 404);
        }

        return response()->json([
            'table' => [
                'number' => $table->table_number,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'status' => $table->status,
                'restaurant' => [
                    'name' => $restaurant->name,
                    'logo' => $restaurant->logo_url,
                    'phone' => $restaurant->phone,
                    'address' => $restaurant->address,
                ]
            ]
        ]);
    }

    /**
     * Passer une commande depuis la table
     */
    public function placeOrder(Request $request, $restaurant_slug, $table_code)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'special_instructions' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $restaurant = Restaurant::where('slug', $restaurant_slug)->first();
            $table = Table::where('restaurant_id', $restaurant->id)
                ->where('table_code', $table_code)
                ->first();

            if (!$table) {
                return response()->json(['error' => 'Table non trouvée'], 404);
            }

            // Calculer le total de la commande
            $totalAmount = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $menuItem = MenuItem::find($item['id']);
                if (!$menuItem) continue;

                $itemTotal = $menuItem->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'price' => $menuItem->price,
                    'total' => $itemTotal,
                ];
            }

            // Créer la commande (security fix: protected fields set separately)
            $order = Order::create([
                'table_id' => $table->id,
                'special_instructions' => $request->special_instructions,
            ]);
            
            // Set protected fields through direct assignment
            $order->restaurant_id = $restaurant->id;
            $order->order_number = 'TBL-' . strtoupper(Str::random(8));
            $order->customer_name = $request->customer_name;
            $order->customer_phone = $request->customer_phone;
            $order->total_amount = $totalAmount;
            $order->status = 'pending';
            $order->order_type = 'table_qr';
            $order->ordered_at = now();
            $order->save();

            // Ajouter les items de la commande
            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            // Notification au restaurant
            TableNotification::create([
                'restaurant_id' => $restaurant->id,
                'table_id' => $table->id,
                'order_id' => $order->id,
                'type' => 'new_order',
                'title' => 'Nouvelle commande',
                'message' => "Nouvelle commande de la table {$table->table_number}",
                'status' => 'unread',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'estimated_time' => 25 // minutes
                ],
                'message' => 'Commande passée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur TableQR placeOrder: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la commande'], 500);
        }
    }

    /**
     * Suivi du statut de commande en temps réel
     */
    public function getOrderStatus($restaurant_slug, $table_code, $order_id)
    {
        $order = Order::with(['items.menuItem'])
            ->where('id', $order_id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Commande non trouvée'], 404);
        }

        $statusMessages = [
            'pending' => 'Commande reçue, en préparation...',
            'confirmed' => 'Commande confirmée par le restaurant',
            'preparing' => 'Commande en cours de préparation',
            'ready' => 'Commande prête, service en cours',
            'served' => 'Commande servie',
            'completed' => 'Commande terminée'
        ];

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_message' => $statusMessages[$order->status] ?? 'Statut inconnu',
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('H:i'),
                'estimated_ready_time' => $order->estimated_ready_time,
                'items' => $order->items->map(function($item) {
                    return [
                        'name' => $item->menuItem->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price
                    ];
                })
            ]
        ]);
    }

    /**
     * Appeler le serveur
     */
    public function callWaiter(Request $request, $restaurant_slug, $table_code)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('table_code', $table_code)
            ->first();

        if (!$table) {
            return response()->json(['error' => 'Table non trouvée'], 404);
        }

        // Créer notification pour appel serveur
        TableNotification::create([
            'restaurant_id' => $restaurant->id,
            'table_id' => $table->id,
            'type' => 'call_waiter',
            'title' => 'Appel serveur',
            'message' => "La table {$table->table_number} demande un serveur",
            'status' => 'unread',
            'priority' => 'high',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le serveur a été appelé et arrivera bientôt'
        ]);
    }

    /**
     * Demander l'addition
     */
    public function requestBill(Request $request, $restaurant_slug, $table_code)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('table_code', $table_code)
            ->first();

        if (!$table) {
            return response()->json(['error' => 'Table non trouvée'], 404);
        }

        // Créer notification pour demande d'addition
        TableNotification::create([
            'restaurant_id' => $restaurant->id,
            'table_id' => $table->id,
            'type' => 'request_bill',
            'title' => 'Demande d\'addition',
            'message' => "La table {$table->table_number} demande l'addition",
            'status' => 'unread',
            'priority' => 'normal',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande d\'addition envoyée au serveur'
        ]);
    }

    /**
     * Évaluer le service de la table
     */
    public function rateService(Request $request, $restaurant_slug, $table_code)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:300'
        ]);

        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('table_code', $table_code)
            ->first();

        if (!$table) {
            return response()->json(['error' => 'Table non trouvée'], 404);
        }

        // Enregistrer l'évaluation
        DB::table('table_ratings')->insert([
            'restaurant_id' => $restaurant->id,
            'table_id' => $table->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merci pour votre évaluation !'
        ]);
    }

    /**
     * Valider un QR code
     */
    public function validateQR($restaurant_slug, $table_code)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)
            ->where('status', 'active')
            ->first();

        if (!$restaurant) {
            return response()->json(['valid' => false, 'message' => 'Restaurant non trouvé']);
        }

        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('table_code', $table_code)
            ->where('status', 'active')
            ->first();

        if (!$table) {
            return response()->json(['valid' => false, 'message' => 'Table non trouvée']);
        }

        return response()->json([
            'valid' => true,
            'restaurant' => $restaurant->name,
            'table' => $table->table_number
        ]);
    }

    /**
     * Menu format JSON pour apps mobiles
     */
    public function getMenuJson($restaurant_slug, $table_code)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('table_code', $table_code)
            ->first();

        if (!$table) {
            return response()->json(['error' => 'Table non trouvée'], 404);
        }

        $menuItems = MenuItem::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->get();

        return response()->json([
            'restaurant' => [
                'name' => $restaurant->name,
                'logo' => $restaurant->logo_url,
                'description' => $restaurant->description,
            ],
            'table' => [
                'number' => $table->table_number,
                'name' => $table->name,
            ],
            'menu' => $menuItems->groupBy('category.name')->map(function($items, $categoryName) {
                return [
                    'category' => $categoryName,
                    'items' => $items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => $item->price,
                            'image' => $item->image_url,
                            'available' => $item->is_available,
                        ];
                    })
                ];
            })->values()
        ]);
    }

    /**
     * API pour placer commande depuis app mobile
     */
    public function placeOrderAPI(Request $request, $restaurant_slug, $table_code)
    {
        return $this->placeOrder($request, $restaurant_slug, $table_code);
    }

    /**
     * Stream de notifications pour WebSocket
     */
    public function getNotificationStream($restaurant_slug)
    {
        // Cette méthode sera étendue pour WebSocket/Server-Sent Events
        return response()->json(['stream_url' => "/ws/tableqr/{$restaurant_slug}"]);
    }

    /**
     * Générer un QR code de test
     */
    public function generateTestQR($restaurant_slug)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant non trouvé'], 404);
        }

        $testTableCode = 'TEST-' . strtoupper(Str::random(6));
        $qrUrl = route('table.menu', [
            'restaurant_slug' => $restaurant_slug,
            'table_code' => $testTableCode
        ]);

        return response()->json([
            'qr_code' => $this->qrService->generate($qrUrl),
            'url' => $qrUrl,
            'message' => 'QR code de test généré'
        ]);
    }

    /**
     * Vérifier la connectivité
     */
    public function ping($restaurant_slug, $table_code)
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'restaurant_slug' => $restaurant_slug,
            'table_code' => $table_code
        ]);
    }

    /**
     * Statistiques publiques du restaurant (si activées)
     */
    public function getPublicStats($restaurant_slug)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();

        if (!$restaurant || !$restaurant->show_public_stats) {
            return response()->json(['error' => 'Statistiques non disponibles'], 404);
        }

        $stats = [
            'total_orders_today' => Order::where('restaurant_id', $restaurant->id)
                ->whereDate('created_at', today())
                ->count(),
            'average_rating' => DB::table('table_ratings')
                ->where('restaurant_id', $restaurant->id)
                ->avg('rating'),
            'active_tables' => Table::where('restaurant_id', $restaurant->id)
                ->where('status', 'active')
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Prévisualisation pour les restaurants
     */
    public function showPreview($restaurant_slug)
    {
        $restaurant = Restaurant::where('slug', $restaurant_slug)->first();

        if (!$restaurant) {
            return view('tableqr.error', [
                'message' => 'Restaurant non trouvé'
            ]);
        }

        return view('tableqr.preview', compact('restaurant'));
    }
}
