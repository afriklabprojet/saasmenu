<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Variants\StoreVariantRequest;
use App\Http\Requests\Variants\UpdateVariantRequest;
use App\Http\Resources\VariantResource;
use App\Http\Resources\VariantCollection;
use App\Models\Variants;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VariantsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $itemId = $request->input('item_id');

        if ($itemId) {
            $item = Item::where('id', $itemId)
                ->where('vendor_id', $this->getVendorId())
                ->where('is_deleted', 0)
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $query = Variants::where('item_id', $itemId);
        } else {
            $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
                ->where('is_deleted', 0)
                ->pluck('id');

            $query = Variants::whereIn('item_id', $vendorItemIds);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $variants = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($variants);
    }

    public function store(StoreVariantRequest $request): JsonResponse
    {
        $variant = Variants::create([
            'item_id' => $request->item_id,
            'name' => $request->name,
            'price' => $request->price,
            'original_price' => $request->original_price ?? $request->price,
            'qty' => $request->qty ?? 0,
            'min_order' => $request->min_order ?? 1,
            'max_order' => $request->max_order ?? 0,
            'is_available' => $request->is_available ?? 1,
            'stock_management' => $request->stock_management ?? 0,
        ]);

        return response()->json([
            'message' => 'Variant created successfully',
            'variant' => $variant
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
            ->where('is_deleted', 0)
            ->pluck('id');

        $variant = Variants::whereIn('item_id', $vendorItemIds)
            ->where('id', $id)
            ->with('item')
            ->first();

        if (!$variant) {
            return response()->json([
                'message' => 'Variant not found'
            ], 404);
        }

        return response()->json($variant);
    }

    public function update(UpdateVariantRequest $request, int $id): JsonResponse
    {
        $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
            ->where('is_deleted', 0)
            ->pluck('id');

        $variant = Variants::whereIn('item_id', $vendorItemIds)
            ->where('id', $id)
            ->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        $variant->update(array_filter([
            'name' => $request->name,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'qty' => $request->qty,
            'min_order' => $request->min_order,
            'max_order' => $request->max_order,
            'is_available' => $request->is_available,
            'stock_management' => $request->stock_management,
        ], fn($value) => $value !== null));

        return response()->json([
            'message' => 'Variant updated successfully',
            'variant' => $variant->fresh()
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
            ->where('is_deleted', 0)
            ->pluck('id');

        $variant = Variants::whereIn('item_id', $vendorItemIds)
            ->where('id', $id)
            ->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        $variant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variant deleted successfully'
        ]);
    }

    private function getVendorId(): int
    {
        $user = Auth::user();
        return $user->type == 4 ? $user->vendor_id : $user->id;
    }
}
