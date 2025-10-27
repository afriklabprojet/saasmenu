<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Restaurant;
use App\Models\Item;
use App\Models\Category;
use App\Models\Order;

class RestaurantController extends Controller
{
    /**
     * Get all restaurants
     */
    public function index(Request $request): JsonResponse
    {
        $restaurants = Restaurant::with(['categories', 'items'])
            ->where('is_active', 1)
            ->when($request->search, function ($query, $search) {
                return $query->where('restaurant_name', 'like', "%{$search}%")
                           ->orWhere('restaurant_address', 'like', "%{$search}%");
            })
            ->when($request->location, function ($query, $location) {
                return $query->where('restaurant_address', 'like', "%{$location}%");
            })
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }

    /**
     * Get restaurant by ID
     */
    public function show($id): JsonResponse
    {
        $restaurant = Restaurant::with(['categories.items', 'tables'])
            ->where('is_active', 1)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $restaurant
        ]);
    }

    /**
     * Get restaurant categories
     */
    public function categories($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $categories = Category::where('user_id', $restaurant->user_id)
            ->with(['items' => function ($query) {
                $query->where('is_available', 1);
            }])
            ->where('is_active', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get restaurant menu items
     */
    public function menu($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $items = Item::where('user_id', $restaurant->user_id)
            ->with(['category'])
            ->where('is_available', 1)
            ->when(request('category_id'), function ($query, $categoryId) {
                return $query->where('cat_id', $categoryId);
            })
            ->when(request('search'), function ($query, $search) {
                return $query->where('item_name', 'like', "%{$search}%");
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Get popular items
     */
    public function popularItems($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('is_available', 1)
            ->orderBy('order_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Search restaurants
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');

        $restaurants = Restaurant::where('is_active', 1)
            ->where(function ($q) use ($query) {
                $q->where('restaurant_name', 'like', "%{$query}%")
                  ->orWhere('restaurant_address', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['categories', 'items'])
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }

    /**
     * Get restaurant orders
     */
    public function orders($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $orders = Order::where('user_id', $restaurant->user_id)
            ->with(['orderItems', 'customer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get restaurant statistics
     */
    public function statistics($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $stats = [
            'total_orders' => Order::where('user_id', $restaurant->user_id)->count(),
            'total_revenue' => Order::where('user_id', $restaurant->user_id)->sum('total'),
            'total_items' => Item::where('user_id', $restaurant->user_id)->count(),
            'active_items' => Item::where('user_id', $restaurant->user_id)->where('is_available', 1)->count(),
            'orders_today' => Order::where('user_id', $restaurant->user_id)
                ->whereDate('created_at', today())->count(),
            'revenue_today' => Order::where('user_id', $restaurant->user_id)
                ->whereDate('created_at', today())->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
