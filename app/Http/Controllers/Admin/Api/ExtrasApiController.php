<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Extras\StoreExtraRequest;
use App\Http\Requests\Extras\UpdateExtraRequest;
use App\Http\Resources\ExtraResource;
use App\Http\Resources\ExtraCollection;
use App\Models\Extra;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtrasApiController extends Controller
{
    /**
     * List all extras for vendor
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Get item_id from request to filter by item
        $itemId = $request->input('item_id');
        
        // Build query based on item relationship
        if ($itemId) {
            // Verify item belongs to vendor
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
            
            $query = Extra::where('item_id', $itemId);
        } else {
            // Get all extras for vendor's items
            $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
                ->where('is_deleted', 0)
                ->pluck('id');
                
            $query = Extra::whereIn('item_id', $vendorItemIds);
        }

        // Filter by availability
        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Load item relationship
        $extras = $query->with('item')
            ->orderBy('reorder_id', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => new ExtraCollection($extras),
            'meta' => [
                'total' => $extras->total(),
                'per_page' => $extras->perPage(),
                'current_page' => $extras->currentPage(),
                'last_page' => $extras->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created extra
     *
     * @param StoreExtraRequest $request
     * @return JsonResponse
     */
    public function store(StoreExtraRequest $request): JsonResponse
    {
        $extra = Extra::create([
            'item_id' => $request->item_id,
            'name' => $request->name,
            'price' => $request->price,
            'is_available' => $request->is_available ?? 1,
            'reorder_id' => $request->reorder_id ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Extra created successfully',
            'data' => new ExtraResource($extra->load('item'))
        ], 201);
    }

    /**
     * Display the specified extra
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Get vendor's item IDs
        $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
            ->where('is_deleted', 0)
            ->pluck('id');

        $extra = Extra::whereIn('item_id', $vendorItemIds)
            ->where('id', $id)
            ->with('item')
            ->first();

        if (!$extra) {
            return response()->json([
                'success' => false,
                'message' => 'Extra not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ExtraResource($extra)
        ]);
    }

    /**
     * Update the specified extra
     *
     * @param UpdateExtraRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateExtraRequest $request, int $id): JsonResponse
    {
        // Get vendor's item IDs
        $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
            ->where('is_deleted', 0)
            ->pluck('id');

        $extra = Extra::whereIn('item_id', $vendorItemIds)
            ->where('id', $id)
            ->first();

        if (!$extra) {
            return response()->json([
                'success' => false,
                'message' => 'Extra not found'
            ], 404);
        }

        $extra->update([
            'name' => $request->name ?? $extra->name,
            'price' => $request->price ?? $extra->price,
            'is_available' => $request->is_available ?? $extra->is_available,
            'reorder_id' => $request->reorder_id ?? $extra->reorder_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Extra updated successfully',
            'data' => new ExtraResource($extra->load('item'))
        ]);
    }

    /**
     * Remove the specified extra
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Get vendor's item IDs
        $vendorItemIds = Item::where('vendor_id', $this->getVendorId())
            ->where('is_deleted', 0)
            ->pluck('id');

        $extra = Extra::whereIn('item_id', $vendorItemIds)
            ->where('id', $id)
            ->first();

        if (!$extra) {
            return response()->json([
                'success' => false,
                'message' => 'Extra not found'
            ], 404);
        }

        $extra->delete();

        return response()->json([
            'success' => true,
            'message' => 'Extra deleted successfully'
        ]);
    }

    /**
     * Get vendor ID (handles both vendor and employee)
     *
     * @return int
     */
    private function getVendorId(): int
    {
        $user = Auth::user();
        
        // Type 2 = Vendor, Type 4 = Employee
        return $user->type == 4 ? $user->vendor_id : $user->id;
    }
}
