<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TableBookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index(Request $request)
    {
        $query = TableBooking::with(['vendor', 'user']);

        // Filter by vendor if not admin
        if (auth()->user()->type != 1) { // 1 = admin
            $query->forVendor(auth()->id());
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->byStatus($request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('booking_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('booking_date', '<=', $request->date_to);
        }

        // Search by customer name/email/phone
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('booking_time', 'desc')
                          ->paginate(20);

        return view('admin.table-booking.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking
     */
    public function create()
    {
        $vendors = User::where('type', 2)->get(); // 2 = vendor
        return view('admin.table-booking.create', compact('vendors'));
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guests_count' => 'required|integer|min:1|max:50',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'special_requests' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Check time slot availability
        if (!TableBooking::isTimeSlotAvailable(
            $validated['vendor_id'],
            $validated['booking_date'],
            $validated['booking_time']
        )) {
            return back()
                ->withInput()
                ->withErrors(['booking_time' => 'Ce créneau horaire n\'est plus disponible.']);
        }

        // Attach authenticated user if exists
        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
        }

        $booking = TableBooking::create($validated);

        return redirect()
            ->route('admin.table-booking.show', $booking)
            ->with('success', 'Réservation créée avec succès.');
    }

    /**
     * Display the specified booking
     */
    public function show(TableBooking $tableBooking)
    {
        $tableBooking->load(['vendor', 'user']);

        // Check authorization
        if (auth()->user()->type != 1 && $tableBooking->vendor_id != auth()->id()) { // 1 = admin
            abort(403, 'Non autorisé.');
        }

        return view('admin.table-booking.show', compact('tableBooking'));
    }

    /**
     * Show the form for editing the specified booking
     */
    public function edit(TableBooking $tableBooking)
    {
        // Check authorization
        if (auth()->user()->type != 1 && $tableBooking->vendor_id != auth()->id()) { // 1 = admin
            abort(403, 'Non autorisé.');
        }

        $vendors = User::where('type', 2)->get(); // 2 = vendor
        return view('admin.table-booking.edit', compact('tableBooking', 'vendors'));
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request, TableBooking $tableBooking)
    {
        // Check authorization
        if (auth()->user()->type != 1 && $tableBooking->vendor_id != auth()->id()) { // 1 = admin
            abort(403, 'Non autorisé.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guests_count' => 'required|integer|min:1|max:50',
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
            'special_requests' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Check time slot availability (excluding current booking)
        if (!TableBooking::isTimeSlotAvailable(
            $validated['vendor_id'],
            $validated['booking_date'],
            $validated['booking_time'],
            $tableBooking->id
        )) {
            return back()
                ->withInput()
                ->withErrors(['booking_time' => 'Ce créneau horaire n\'est plus disponible.']);
        }

        $tableBooking->update($validated);

        return redirect()
            ->route('admin.table-booking.show', $tableBooking)
            ->with('success', 'Réservation mise à jour avec succès.');
    }

    /**
     * Remove the specified booking
     */
    public function destroy(TableBooking $tableBooking)
    {
        // Check authorization
        if (auth()->user()->type != 1 && $tableBooking->vendor_id != auth()->id()) { // 1 = admin
            abort(403, 'Non autorisé.');
        }

        $tableBooking->delete();

        return redirect()
            ->route('admin.table-booking.index')
            ->with('success', 'Réservation supprimée avec succès.');
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, TableBooking $tableBooking)
    {
        // Check authorization
        if (auth()->user()->type != 1 && $tableBooking->vendor_id != auth()->id()) { // 1 = admin
            abort(403, 'Non autorisé.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $tableBooking->update($validated);

        return back()->with('success', 'Statut de la réservation mis à jour.');
    }

    /**
     * Show customer booking form
     */
    public function customerCreate($vendorSlug)
    {
        $vendor = User::where('unique_slug', $vendorSlug)->firstOrFail();
        return view('web.table-booking.form', compact('vendor'));
    }

    /**
     * Store customer booking
     */
    public function customerStore(Request $request, $vendorSlug)
    {
        $vendor = User::where('unique_slug', $vendorSlug)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guests_count' => 'required|integer|min:1|max:50',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        // Check time slot availability
        if (!TableBooking::isTimeSlotAvailable(
            $vendor->id,
            $validated['booking_date'],
            $validated['booking_time']
        )) {
            return back()
                ->withInput()
                ->withErrors(['booking_time' => 'Ce créneau horaire n\'est plus disponible.']);
        }

        $validated['vendor_id'] = $vendor->id;
        $validated['status'] = 'pending';

        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
        }

        $booking = TableBooking::create($validated);

        return redirect()
            ->back()
            ->with('success', 'Votre réservation a été envoyée avec succès. Nous vous contacterons bientôt.');
    }
}
