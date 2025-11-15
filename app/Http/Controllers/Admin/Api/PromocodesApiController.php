<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PromocodesApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $query = Promocode::where('vendor_id', $vendorId);

        if ($request->has('is_active')) {
            $now = now();
            if ($request->is_active) {
                $query->where('start_date', '<=', $now)
                      ->where('exp_date', '>=', $now);
            }
        }

        $promocodes = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($promocodes);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'offer_name' => 'required|string|max:255',
            'offer_code' => 'required|string|max:255|unique:promocodes,offer_code',
            'offer_type' => 'required|integer',
            'offer_amount' => 'required|numeric|min:0',
            'min_amount' => 'required|integer|min:0',
            'usage_type' => 'required|integer',
            'usage_limit' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'exp_date' => 'required|date|after:start_date',
        ]);

        $promocode = Promocode::create([
            'vendor_id' => $this->getVendorId(),
            'offer_name' => $request->offer_name,
            'offer_code' => strtoupper($request->offer_code),
            'offer_type' => $request->offer_type,
            'offer_amount' => $request->offer_amount,
            'min_amount' => $request->min_amount,
            'usage_type' => $request->usage_type,
            'usage_limit' => $request->usage_limit,
            'start_date' => $request->start_date,
            'exp_date' => $request->exp_date,
        ]);

        return response()->json([
            'message' => 'Promocode created successfully',
            'promocode' => $promocode
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $promocode = Promocode::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$promocode) {
            return response()->json([
                'success' => false,
                'message' => 'Promocode not found'
            ], 404);
        }

        return response()->json($promocode);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $promocode = Promocode::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$promocode) {
            return response()->json([
                'message' => 'Promocode not found'
            ], 404);
        }

        $updateData = $request->only([
            'offer_name', 'offer_code', 'offer_type', 'offer_amount',
            'min_amount', 'usage_type', 'usage_limit', 'start_date', 'exp_date', 'is_active'
        ]);

        $promocode->update(array_filter($updateData, fn($v) => $v !== null));

        return response()->json([
            'message' => 'Promocode updated successfully',
            'promocode' => $promocode->fresh()
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $promocode = Promocode::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$promocode) {
            return response()->json([
                'message' => 'Promocode not found'
            ], 404);
        }

        $promocode->delete();

        return response()->json([
            'message' => 'Promocode deleted successfully'
        ]);
    }

    private function getVendorId(): int
    {
        $user = Auth::user();
        return $user->type == 4 ? $user->vendor_id : $user->id;
    }
}
