<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\Restaurant;
use App\Models\Customer;

class OrderController extends Controller
{
    /**
     * Create new order
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_type' => 'required|in:delivery,pickup',
            'delivery_address' => 'required_if:delivery_type,delivery|string',
            'payment_method' => 'required|string',
            'special_instructions' => 'nullable|string',
        ]);

        $customer = $request->user();
        $restaurant = Restaurant::findOrFail($request->restaurant_id);

        // Calculate total
        $total = 0;
        $orderItems = [];

        foreach ($request->items as $itemData) {
            $item = Item::findOrFail($itemData['item_id']);
            $subtotal = $item->item_price * $itemData['quantity'];
            $total += $subtotal;

            $orderItems[] = [
                'item_id' => $item->id,
                'quantity' => $itemData['quantity'],
                'price' => $item->item_price,
                'subtotal' => $subtotal,
                'item_name' => $item->item_name,
            ];
        }

        // Add delivery fee if applicable
        $deliveryFee = 0;
        if ($request->delivery_type === 'delivery') {
            $deliveryFee = 5.00; // Default delivery fee
            $total += $deliveryFee;
        }

        // Create order (security fix: protected fields set separately)
        $order = Order::create([
            'user_id' => $restaurant->user_id,
            'customer_id' => $customer->id,
            'delivery_type' => $request->delivery_type,
            'delivery_address' => $request->delivery_address,
            'special_instructions' => $request->special_instructions,
        ]);
        
        // Set protected fields through direct assignment
        $order->restaurant_id = $request->restaurant_id;
        $order->order_number = 'ORD' . time();
        $order->status = 'pending';
        $order->subtotal = $total - $deliveryFee;
        $order->delivery_fee = $deliveryFee;
        $order->total = $total;
        $order->payment_method = $request->payment_method;
        $order->payment_status = 'pending';
        $order->estimated_delivery_time = now()->addMinutes(30);
        $order->save();

        // Create order items
        foreach ($orderItems as $itemData) {
            OrderItem::create(array_merge($itemData, [
                'order_id' => $order->id,
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => $order->load(['orderItems', 'restaurant', 'customer'])
        ], 201);
    }

    /**
     * Get order details
     */
    public function show($id): JsonResponse
    {
        $order = Order::with(['orderItems.item', 'restaurant', 'customer'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut de commande mis à jour',
            'data' => $order->fresh()
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $customer = $request->user();

        // Check if customer owns this order
        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Check if order can be cancelled
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas être annulée'
            ], 400);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason ?? 'Annulé par le client'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée avec succès',
            'data' => $order->fresh()
        ]);
    }

    /**
     * Get customer orders
     */
    public function customerOrders(Request $request): JsonResponse
    {
        $customer = $request->user();

        $orders = Order::where('customer_id', $customer->id)
            ->with(['orderItems.item', 'restaurant'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Track order
     */
    public function track(Request $request, $id): JsonResponse
    {
        $order = Order::with(['restaurant', 'orderItems'])
            ->findOrFail($id);

        $customer = $request->user();

        // Check if customer owns this order
        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $trackingInfo = [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'restaurant' => $order->restaurant->only(['restaurant_name', 'restaurant_address', 'restaurant_phone']),
            'timeline' => $this->getOrderTimeline($order),
        ];

        return response()->json([
            'success' => true,
            'data' => $trackingInfo
        ]);
    }

    /**
     * Rate order
     */
    public function rate(Request $request, $id): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);
        $customer = $request->user();

        // Check if customer owns this order
        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Check if order is delivered
        if ($order->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Vous pouvez seulement évaluer une commande livrée'
            ], 400);
        }

        $order->update([
            'rating' => $request->rating,
            'review' => $request->review,
            'rated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Évaluation ajoutée avec succès',
            'data' => $order->fresh()
        ]);
    }

    /**
     * Get order timeline
     */
    private function getOrderTimeline($order): array
    {
        $timeline = [
            [
                'status' => 'pending',
                'label' => 'Commande reçue',
                'completed' => true,
                'time' => $order->created_at
            ],
            [
                'status' => 'confirmed',
                'label' => 'Commande confirmée',
                'completed' => in_array($order->status, ['confirmed', 'preparing', 'ready', 'delivered']),
                'time' => $order->status === 'confirmed' ? $order->updated_at : null
            ],
            [
                'status' => 'preparing',
                'label' => 'En préparation',
                'completed' => in_array($order->status, ['preparing', 'ready', 'delivered']),
                'time' => $order->status === 'preparing' ? $order->updated_at : null
            ],
            [
                'status' => 'ready',
                'label' => 'Prêt',
                'completed' => in_array($order->status, ['ready', 'delivered']),
                'time' => $order->status === 'ready' ? $order->updated_at : null
            ],
            [
                'status' => 'delivered',
                'label' => 'Livré',
                'completed' => $order->status === 'delivered',
                'time' => $order->status === 'delivered' ? $order->updated_at : null
            ]
        ];

        return $timeline;
    }
}
