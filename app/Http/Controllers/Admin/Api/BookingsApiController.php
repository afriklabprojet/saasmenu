<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingsApiController extends Controller
{
    /**
     * Display a listing of bookings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $query = Booking::where('vendor_id', $vendorId);

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }

        // Filter by booking number
        if ($request->has('booking_number')) {
            $query->where('booking_number', 'LIKE', '%' . $request->booking_number . '%');
        }

        $bookings = $query->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($bookings);
    }

    /**
     * Display the specified booking
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $booking = Booking::where('vendor_id', $vendorId)
            ->findOrFail($id);

        return response()->json($booking);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $booking = Booking::where('vendor_id', $vendorId)
            ->findOrFail($id);

        $validated = $request->validate([
            'payment_status' => 'sometimes|integer|in:0,1,2',
            'message' => 'nullable|string',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking->fresh()
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $vendorId = $this->getVendorId();

        $booking = Booking::where('vendor_id', $vendorId)
            ->findOrFail($id);

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully'
        ]);
    }

    private function getVendorId(): int
    {
        $user = auth()->user();
        return $user->type == 4 ? $user->vendor_id : $user->id;
    }
}
