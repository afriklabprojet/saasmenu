<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $query = Cart::where('vendor_id', $vendorId);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        $carts = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($carts);
    }

    public function destroy(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $cart = Cart::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'message' => 'Cart item deleted successfully'
        ]);
    }

    private function getVendorId(): int
    {
        $user = Auth::user();
        return $user->type == 4 ? $user->vendor_id : $user->id;
    }
}
