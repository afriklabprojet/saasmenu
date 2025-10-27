<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\POSTerminal;
use App\Models\POSSession;
use App\Http\Requests\Api\CreatePOSSessionRequest;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\CheckoutRequest;
use Illuminate\Http\Request;

class PosApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pos/terminals",
     *     summary="Get POS terminals",
     *     description="Retrieve all POS terminals for the authenticated restaurant",
     *     tags={"POS System"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by terminal status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of POS terminals",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Terminals retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/POSTerminal")
     *             ),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function getTerminals(Request $request)
    {
        $restaurantId = $request->user()->restaurant_id ?? $request->header('X-Restaurant-ID');

        $query = POSTerminal::where('restaurant_id', $restaurantId);

        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $terminals = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Terminals retrieved successfully',
            'data' => $terminals->items(),
            'meta' => [
                'current_page' => $terminals->currentPage(),
                'last_page' => $terminals->lastPage(),
                'per_page' => $terminals->perPage(),
                'total' => $terminals->total(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/pos/sessions",
     *     summary="Create POS session",
     *     description="Start a new POS session on a terminal",
     *     tags={"POS System"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="terminal_id", type="integer", example=1),
     *             @OA\Property(property="staff_name", type="string", example="John Cashier"),
     *             @OA\Property(property="opening_balance", type="number", format="float", example=100.00),
     *             @OA\Property(property="notes", type="string", example="Morning shift start")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="POS session created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="POS session started successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/POSSession")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function createSession(CreatePOSSessionRequest $request)
    {
        $restaurantId = $request->user()->restaurant_id ?? $request->header('X-Restaurant-ID');

        $session = POSSession::create([
            'terminal_id' => $request->terminal_id,
            'restaurant_id' => $restaurantId,
            'user_id' => $request->user()->id,
            'staff_name' => $request->staff_name,
            'opening_balance' => $request->opening_balance,
            'current_balance' => $request->opening_balance,
            'status' => 'active',
            'cart_items' => [],
            'notes' => $request->notes,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'POS session started successfully',
            'data' => $session->fresh(),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/pos/sessions/{sessionId}",
     *     summary="Get POS session details",
     *     description="Retrieve details of a specific POS session",
     *     tags={"POS System"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         description="POS session ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="POS session details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/POSSession")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Session not found")
     * )
     */
    public function getSession($sessionId)
    {
        $session = POSSession::findOrFail($sessionId);

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/pos/sessions/{sessionId}/cart",
     *     summary="Add item to cart",
     *     description="Add an item to the POS session cart",
     *     tags={"POS System"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         description="POS session ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Burger Deluxe"),
     *             @OA\Property(property="price", type="number", format="float", example=15.99),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="modifiers", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Item added to cart"),
     *             @OA\Property(property="data", ref="#/components/schemas/POSSession")
     *         )
     *     )
     * )
     */
    public function addCartItem($sessionId, AddCartItemRequest $request)
    {
        $session = POSSession::findOrFail($sessionId);

        $cartItems = $session->cart_items ?? [];
        $cartItems[] = [
            'item_id' => $request->item_id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'modifiers' => $request->modifiers ?? [],
            'subtotal' => $request->price * $request->quantity,
            'added_at' => now()->toISOString(),
        ];

        $session->update(['cart_items' => $cartItems]);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $session->fresh(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/pos/sessions/{sessionId}/checkout",
     *     summary="Process checkout",
     *     description="Process payment and complete the POS transaction",
     *     tags={"POS System"},
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         description="POS session ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_method", type="string", enum={"cash", "card", "digital"}, example="cash"),
     *             @OA\Property(property="amount", type="number", format="float", example=31.98),
     *             @OA\Property(property="table_number", type="integer", example=5),
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="discount", type="number", format="float", example=2.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transaction completed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="transaction_id", type="string", example="TXN-1634567890"),
     *                 @OA\Property(property="receipt_number", type="string", example="RCP-001234"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=31.98),
     *                 @OA\Property(property="payment_method", type="string", example="cash"),
     *                 @OA\Property(property="completed_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function checkout($sessionId, CheckoutRequest $request)
    {
        $session = POSSession::findOrFail($sessionId);

        $transactionId = 'TXN-' . time();
        $receiptNumber = 'RCP-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $session->update([
            'transactions' => array_merge($session->transactions ?? [], [[
                'transaction_id' => $transactionId,
                'receipt_number' => $receiptNumber,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'table_number' => $request->table_number,
                'customer_name' => $request->customer_name,
                'discount' => $request->discount ?? 0,
                'items' => $session->cart_items,
                'completed_at' => now()->toISOString(),
            ]]),
            'current_balance' => $session->current_balance + $request->amount,
            'cart_items' => [], // Clear cart after checkout
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction completed',
            'data' => [
                'transaction_id' => $transactionId,
                'receipt_number' => $receiptNumber,
                'total_amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'completed_at' => now()->toISOString(),
            ]
        ]);
    }
}

/**
 * @OA\Schema(
 *     schema="POSTerminal",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="restaurant_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Main Counter Terminal"),
 *     @OA\Property(property="identifier", type="string", example="POS_1_1"),
 *     @OA\Property(property="location", type="string", example="Front Counter"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="last_used_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="POSSession",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="terminal_id", type="integer", example=1),
 *     @OA\Property(property="restaurant_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="staff_name", type="string", example="John Cashier"),
 *     @OA\Property(property="opening_balance", type="number", format="float", example=100.00),
 *     @OA\Property(property="current_balance", type="number", format="float", example=150.00),
 *     @OA\Property(property="status", type="string", enum={"active", "closed"}, example="active"),
 *     @OA\Property(
 *         property="cart_items",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="item_id", type="integer"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="price", type="number", format="float"),
 *             @OA\Property(property="quantity", type="integer"),
 *             @OA\Property(property="subtotal", type="number", format="float")
 *         )
 *     ),
 *     @OA\Property(property="transactions", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="started_at", type="string", format="date-time"),
 *     @OA\Property(property="closed_at", type="string", format="date-time", nullable=true)
 * )
 */
