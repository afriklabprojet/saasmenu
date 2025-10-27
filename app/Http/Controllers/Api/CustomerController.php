<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Models\Order;
use App\Models\LoyaltyCard;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Get customer profile
     */
    public function profile(Request $request): JsonResponse
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'data' => $customer->load(['orders', 'loyaltyCards'])
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string|max:100',
            'postal_code' => 'sometimes|nullable|string|max:20',
        ]);

        $customer->update($request->only(['name', 'phone', 'address', 'city', 'postal_code']));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => $customer->fresh()
        ]);
    }

    /**
     * Get customer orders
     */
    public function orders(Request $request): JsonResponse
    {
        $customer = $request->user();

        $orders = Order::where('customer_id', $customer->id)
            ->with(['orderItems.item', 'restaurant'])
            ->orderBy('created_at', 'desc')
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get specific order
     */
    public function getOrder(Request $request, $orderId): JsonResponse
    {
        $customer = $request->user();

        $order = Order::where('customer_id', $customer->id)
            ->with(['orderItems.item', 'restaurant'])
            ->findOrFail($orderId);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Get customer loyalty cards
     */
    public function loyaltyCards(Request $request): JsonResponse
    {
        $customer = $request->user();

        $loyaltyCards = LoyaltyCard::where('customer_id', $customer->id)
            ->with(['restaurant'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $loyaltyCards
        ]);
    }

    /**
     * Get customer favorites
     */
    public function favorites(Request $request): JsonResponse
    {
        $customer = $request->user();

        $favorites = $customer->favorites()
            ->with(['item', 'restaurant'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Add to favorites
     */
    public function addFavorite(Request $request): JsonResponse
    {
        $customer = $request->user();

        $request->validate([
            'item_id' => 'required|exists:items,id',
        ]);

        $favorite = $customer->favorites()->firstOrCreate([
            'item_id' => $request->item_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ajouté aux favoris',
            'data' => $favorite->load(['item'])
        ]);
    }

    /**
     * Remove from favorites
     */
    public function removeFavorite(Request $request, $itemId): JsonResponse
    {
        $customer = $request->user();

        $customer->favorites()->where('item_id', $itemId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Retiré des favoris'
        ]);
    }

    /**
     * Get customer addresses
     */
    public function addresses(Request $request): JsonResponse
    {
        $customer = $request->user();

        $addresses = $customer->addresses ?? [];

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * Add customer address
     */
    public function addAddress(Request $request): JsonResponse
    {
        $customer = $request->user();

        $request->validate([
            'label' => 'required|string|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        $addresses = $customer->addresses ?? [];

        if ($request->is_default) {
            foreach ($addresses as &$addr) {
                $addr['is_default'] = false;
            }
        }

        $addresses[] = array_merge($request->only(['label', 'address', 'city', 'postal_code']), [
            'id' => uniqid(),
            'is_default' => $request->is_default ?? false,
        ]);

        $customer->update(['addresses' => $addresses]);

        return response()->json([
            'success' => true,
            'message' => 'Adresse ajoutée avec succès',
            'data' => $addresses
        ]);
    }

    /**
     * Delete customer account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $customer = $request->user();

        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect'
            ], 400);
        }

        // Anonymize customer data instead of deleting
        $customer->update([
            'name' => 'Compte supprimé',
            'email' => 'deleted_' . time() . '@example.com',
            'phone' => null,
            'address' => null,
            'status' => 0,
        ]);

        $customer->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compte supprimé avec succès'
        ]);
    }
}
