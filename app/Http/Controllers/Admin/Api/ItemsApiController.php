<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Items Management
 *
 * API endpoints for managing restaurant items/products
 */
class ItemsApiController extends Controller
{
    /**
     * Display a listing of items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $query = Item::where('vendor_id', $vendorId)
            ->where('is_deleted', 0);

        // Filter by category
        if ($request->has('cat_id')) {
            $query->where('cat_id', $request->cat_id);
        }

        // Filter by availability
        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Load relations (only category_info exists in current schema)
        $items = $query->with(['category_info'])
            ->orderBy('reorder_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => new ItemCollection($items),
            'meta' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created item
     *
     * @param StoreItemRequest $request
     * @return JsonResponse
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        // Generate unique slug
        $slug = Str::slug($request->name) . '-' . Str::random(5);

        $item = new Item();
        $item->vendor_id = $vendorId;
        $item->cat_id = $request->cat_id;
        $item->name = $request->name;
        $item->slug = $slug;
        $item->description = $request->description;

        // Guarded fields - set explicitly with business logic
        $item->price = $request->price;
        $item->original_price = $request->original_price ?? $request->price;
        $item->is_available = $request->is_available ?? 1;

        // Stock management
        $item->stock_management = $request->stock_management ?? 0;
        if ($item->stock_management) {
            $item->qty = $request->qty ?? 0;
            $item->min_order = $request->min_order ?? 1;
            $item->max_order = $request->max_order ?? 0;
            $item->low_qty = $request->low_qty ?? 0;
        }

        $item->tax = $request->tax;
        $item->sku = $request->sku;
        $item->reorder_id = $request->reorder_id ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = 'item-' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('admin-assets/images/item', $imageName, 'public');
            $item->image = $imageName;
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully',
            'data' => new ItemResource($item->load(['category_info']))
        ], 201);
    }

    /**
     * Display the specified item
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $item = Item::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->where('is_deleted', 0)
            ->with(['category_info'])
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ItemResource($item)
        ]);
    }

    /**
     * Update the specified item
     *
     * @param UpdateItemRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateItemRequest $request, int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $item = Item::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->where('is_deleted', 0)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        // Update basic fields
        $item->name = $request->name ?? $item->name;
        $item->cat_id = $request->cat_id ?? $item->cat_id;
        $item->description = $request->description ?? $item->description;

        // Guarded fields - update with business logic
        if ($request->has('price')) {
            $item->price = $request->price;
        }
        if ($request->has('original_price')) {
            $item->original_price = $request->original_price;
        }
        if ($request->has('is_available')) {
            $item->is_available = $request->is_available;
        }

        // Stock management
        if ($request->has('stock_management')) {
            $item->stock_management = $request->stock_management;
        }
        if ($item->stock_management) {
            if ($request->has('qty')) {
                $item->qty = $request->qty;
            }
            $item->min_order = $request->min_order ?? $item->min_order;
            $item->max_order = $request->max_order ?? $item->max_order;
            $item->low_qty = $request->low_qty ?? $item->low_qty;
        }

        $item->tax = $request->tax ?? $item->tax;
        $item->sku = $request->sku ?? $item->sku;
        $item->reorder_id = $request->reorder_id ?? $item->reorder_id;

        // Update slug if name changed
        if ($request->has('name') && $request->name !== $item->getOriginal('name')) {
            $item->slug = Str::slug($request->name) . '-' . Str::random(5);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($item->image) {
                Storage::disk('public')->delete('admin-assets/images/item/' . $item->image);
            }

            $imageName = 'item-' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('admin-assets/images/item', $imageName, 'public');
            $item->image = $imageName;
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'data' => new ItemResource($item->load(['category_info']))
        ]);
    }

    /**
     * Remove the specified item (soft delete)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $item = Item::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->where('is_deleted', 0)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        // Soft delete
        $item->is_deleted = 1;
        $item->is_available = 0;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    }

    /**
     * Get vendor ID from authenticated user
     *
     * @return int
     */
    protected function getVendorId(): int
    {
        $user = Auth::user();

        // If employee, get vendor_id from user record
        if ($user->type == 4) {
            return $user->vendor_id;
        }

        // If vendor, return user id
        return $user->id;
    }
}
