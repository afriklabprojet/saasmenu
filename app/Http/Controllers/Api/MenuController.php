<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Item;
use App\Models\Category;
use App\Models\Restaurant;

class MenuController extends Controller
{
    /**
     * Get menu by restaurant
     */
    public function getByRestaurant($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $categories = Category::where('user_id', $restaurant->user_id)
            ->with(['items' => function ($query) {
                $query->where('is_available', 1)
                      ->select('id', 'cat_id', 'item_name', 'item_price', 'item_description', 'item_image', 'is_available');
            }])
            ->where('is_active', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant->only(['id', 'restaurant_name', 'restaurant_image']),
                'categories' => $categories
            ]
        ]);
    }

    /**
     * Get all categories
     */
    public function categories($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $categories = Category::where('user_id', $restaurant->user_id)
            ->where('is_active', 1)
            ->withCount(['items' => function ($query) {
                $query->where('is_available', 1);
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get items by category
     */
    public function itemsByCategory($restaurantId, $categoryId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $category = Category::where('user_id', $restaurant->user_id)
            ->where('id', $categoryId)
            ->firstOrFail();

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('cat_id', $categoryId)
            ->where('is_available', 1)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'items' => $items
            ]
        ]);
    }

    /**
     * Get single item
     */
    public function getItem($restaurantId, $itemId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $item = Item::where('user_id', $restaurant->user_id)
            ->where('id', $itemId)
            ->with(['category'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    /**
     * Search menu items
     */
    public function search(Request $request, $restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $query = $request->get('q');

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('is_available', 1)
            ->where(function ($q) use ($query) {
                $q->where('item_name', 'like', "%{$query}%")
                  ->orWhere('item_description', 'like', "%{$query}%");
            })
            ->with(['category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Get popular items
     */
    public function popular($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('is_available', 1)
            ->orderBy('order_count', 'desc')
            ->limit(10)
            ->with(['category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Get featured items
     */
    public function featured($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('is_available', 1)
            ->where('is_featured', 1)
            ->with(['category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Get new items
     */
    public function newItems($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('is_available', 1)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Filter items
     */
    public function filter(Request $request, $restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $items = Item::where('user_id', $restaurant->user_id)
            ->where('is_available', 1)
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('cat_id', $categoryId);
            })
            ->when($request->min_price, function ($query, $minPrice) {
                return $query->where('item_price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                return $query->where('item_price', '<=', $maxPrice);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'price_low':
                        return $query->orderBy('item_price', 'asc');
                    case 'price_high':
                        return $query->orderBy('item_price', 'desc');
                    case 'popular':
                        return $query->orderBy('order_count', 'desc');
                    case 'newest':
                        return $query->orderBy('created_at', 'desc');
                    default:
                        return $query->orderBy('item_name', 'asc');
                }
            })
            ->with(['category'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Get menu statistics
     */
    public function statistics($restaurantId): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $stats = [
            'total_categories' => Category::where('user_id', $restaurant->user_id)->count(),
            'total_items' => Item::where('user_id', $restaurant->user_id)->count(),
            'available_items' => Item::where('user_id', $restaurant->user_id)->where('is_available', 1)->count(),
            'featured_items' => Item::where('user_id', $restaurant->user_id)->where('is_featured', 1)->count(),
            'average_price' => Item::where('user_id', $restaurant->user_id)->where('is_available', 1)->avg('item_price'),
            'price_range' => [
                'min' => Item::where('user_id', $restaurant->user_id)->where('is_available', 1)->min('item_price'),
                'max' => Item::where('user_id', $restaurant->user_id)->where('is_available', 1)->max('item_price'),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
