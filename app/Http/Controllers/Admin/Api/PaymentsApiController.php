<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $query = Payment::where('vendor_id', $vendorId);

        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($payments);
    }

    public function show(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $payment = Payment::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment method not found'
            ], 404);
        }

        return response()->json($payment);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $payment = Payment::where('id', $id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$payment) {
            return response()->json([
                'message' => 'Payment method not found'
            ], 404);
        }

        $updateData = $request->only([
            'is_available',
            'environment',
            'key',
            'public_key',
            'secret_key',
            'currency'
        ]);

        // Map 'key' to 'public_key' if provided
        if (isset($updateData['key'])) {
            $updateData['public_key'] = $updateData['key'];
            unset($updateData['key']);
        }

        $payment->update(array_filter($updateData, fn($v) => $v !== null));

        return response()->json([
            'message' => 'Payment method updated successfully',
            'payment' => $payment->fresh()
        ]);
    }

    private function getVendorId(): int
    {
        $user = Auth::user();
        return $user->type == 4 ? $user->vendor_id : $user->id;
    }
}
