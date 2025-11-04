<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Payment;
use App\Models\CustomStatus;
use App\Helpers\helper;
use App\Services\AuditService;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display checkout page
     */
    public function checkout(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        $cartdata = $this->getCartItems($vdata);
        
        if ($cartdata->isEmpty()) {
            return redirect('/cart')->with('error', 'Votre panier est vide');
        }

        $paymentmethods = Payment::where('vendor_id', $vdata)
                                ->where('is_available', 1)
                                ->get();

        return view('front.checkout', compact('settingdata', 'cartdata', 'vdata', 'paymentmethods'));
    }

    /**
     * Create new order
     */
    public function create(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'mobile' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'payment_method' => 'required|integer|exists:payments,id',
            'order_type' => 'required|in:1,2', // 1=delivery, 2=pickup
        ]);

        $vdata = Session::get('restaurant_id');
        $cartItems = $this->getCartItems($vdata);

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'Panier vide'], 400);
        }

        try {
            DB::beginTransaction();

            // Create order
            $order = $this->createOrder($request, $vdata);
            
            // Create order details
            $this->createOrderDetails($order, $cartItems);
            
            // Clear cart
            $this->clearCart($vdata);
            
            // Log order creation
            AuditService::logAdminAction(
                'CREATE_ORDER',
                'Order',
                [
                    'order_number' => $order->order_number,
                    'total' => $order->grand_total,
                    'payment_method' => $request->payment_method
                ],
                $order->id
            );

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Commande créée avec succès',
                'order_number' => $order->order_number,
                'redirect_url' => route('order.success', $order->order_number)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            AuditService::logSecurityEvent('ORDER_CREATION_FAILED', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json(['status' => 0, 'message' => 'Erreur lors de la création de la commande'], 500);
        }
    }

    /**
     * Order success page
     */
    public function success(Request $request)
    {
        $orderNumber = $request->route('order_number');
        $vdata = Session::get('restaurant_id');

        $order = Order::where('order_number', $orderNumber)
                     ->where('vendor_id', $vdata)
                     ->with('orderdetails.item')
                     ->first();

        if (!$order) {
            return redirect('/')->with('error', 'Commande introuvable');
        }

        $settingdata = helper::appdata($vdata);

        return view('front.order-success', compact('order', 'settingdata', 'vdata'));
    }

    /**
     * Track order
     */
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:50',
        ]);

        $vdata = Session::get('restaurant_id');
        
        $order = Order::select([
                'order_number',
                DB::raw('DATE_FORMAT(created_at, "%d %M %Y") as date'),
                'address', 'building', 'landmark', 'pincode', 'order_type',
                'id', 'discount_amount', 'status', 'status_type', 'order_notes',
                'tax', 'tax_name', 'delivery_charge', 'couponcode', 'offer_type',
                'sub_total', 'grand_total', 'customer_name', 'customer_email', 'mobile'
            ])
            ->where('order_number', $request->order_number)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$order) {
            return response()->json(['status' => 0, 'message' => 'Commande introuvable'], 404);
        }

        $orderDetails = OrderDetails::with('item')
                                  ->where('order_id', $order->id)
                                  ->get();

        $customStatus = CustomStatus::where('vendor_id', $vdata)
                                  ->where('type', 1)
                                  ->where('is_available', 1)
                                  ->orderBy('order_sequence')
                                  ->get();

        return response()->json([
            'status' => 1,
            'order' => $order,
            'order_details' => $orderDetails,
            'custom_status' => $customStatus
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $orderNumber)
    {
        $vdata = Session::get('restaurant_id');
        
        $order = Order::where('order_number', $orderNumber)
                     ->where('vendor_id', $vdata)
                     ->first();

        if (!$order) {
            return response()->json(['status' => 0, 'message' => 'Commande introuvable'], 404);
        }

        // Check if order can be cancelled (within time limit)
        if (!$this->canCancelOrder($order)) {
            return response()->json(['status' => 0, 'message' => 'Cette commande ne peut plus être annulée'], 400);
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->status_type = 5; // Cancelled
            $order->save();

            // Restore stock if needed
            $this->restoreStock($order);

            // Log cancellation
            AuditService::logAdminAction(
                'CANCEL_ORDER',
                'Order',
                ['reason' => 'Customer cancellation'],
                $order->id
            );

            DB::commit();

            return response()->json(['status' => 1, 'message' => 'Commande annulée avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Erreur lors de l\'annulation'], 500);
        }
    }

    /**
     * Get cart items for vendor
     */
    private function getCartItems($vendorId)
    {
        $query = Cart::with(['item', 'variants'])
                    ->where('vendor_id', $vendorId)
                    ->where('buynow', 0);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', Session::getId());
        }

        return $query->get();
    }

    /**
     * Create order
     */
    private function createOrder(Request $request, $vendorId)
    {
        $cartItems = $this->getCartItems($vendorId);
        $subTotal = $this->calculateSubTotal($cartItems);
        $taxAmount = $this->calculateTax($subTotal, $vendorId);
        $deliveryCharge = $this->calculateDeliveryCharge($request->order_type, $vendorId);
        $grandTotal = $subTotal + $taxAmount + $deliveryCharge;

        $order = new Order();
        $order->order_number = $this->generateOrderNumber();
        $order->vendor_id = $vendorId;
        $order->customer_name = $request->customer_name;
        $order->customer_email = $request->customer_email;
        $order->mobile = $request->mobile;
        $order->address = $request->address;
        $order->building = $request->building;
        $order->landmark = $request->landmark;
        $order->pincode = $request->pincode;
        $order->order_type = $request->order_type;
        $order->sub_total = $subTotal;
        $order->tax = $taxAmount;
        $order->delivery_charge = $deliveryCharge;
        $order->grand_total = $grandTotal;
        $order->payment_method = $request->payment_method;
        $order->status_type = 1; // Pending
        $order->is_available = 1;
        $order->save();

        return $order;
    }

    /**
     * Create order details
     */
    private function createOrderDetails($order, $cartItems)
    {
        foreach ($cartItems as $cart) {
            $orderDetail = new OrderDetails();
            $orderDetail->order_id = $order->id;
            $orderDetail->item_id = $cart->item_id;
            $orderDetail->item_name = $cart->item->item_name;
            $orderDetail->item_image = $cart->item->item_image;
            $orderDetail->item_price = $cart->price;
            $orderDetail->extras_id = $cart->extras_id;
            $orderDetail->extras_name = $cart->extras_name;
            $orderDetail->extras_price = $cart->extras_price;
            $orderDetail->variants_id = $cart->variants_id;
            $orderDetail->variants_name = $cart->variants ? $cart->variants->name : '';
            $orderDetail->variants_price = $cart->variants ? $cart->variants->price : 0;
            $orderDetail->qty = $cart->qty;
            $orderDetail->save();

            // Update stock
            $this->updateStock($cart);
        }
    }

    /**
     * Clear cart after order
     */
    private function clearCart($vendorId)
    {
        $query = Cart::where('vendor_id', $vendorId)->where('buynow', 0);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', Session::getId());
        }

        $query->delete();
    }

    /**
     * Calculate subtotal
     */
    private function calculateSubTotal($cartItems)
    {
        $total = 0;
        foreach ($cartItems as $cart) {
            $total += ($cart->price + $cart->extras_price) * $cart->qty;
        }
        return $total;
    }

    /**
     * Calculate tax
     */
    private function calculateTax($subtotal, $vendorId)
    {
        // Implement tax calculation logic based on vendor settings
        return 0; // Placeholder
    }

    /**
     * Calculate delivery charge
     */
    private function calculateDeliveryCharge($orderType, $vendorId)
    {
        if ($orderType == 2) { // Pickup
            return 0;
        }
        
        // Implement delivery charge calculation
        return 0; // Placeholder
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        return 'ORD-' . time() . '-' . rand(1000, 9999);
    }

    /**
     * Update stock after order
     */
    private function updateStock($cart)
    {
        $item = $cart->item;
        
        if ($item->stock_management == 1) {
            if ($cart->variants_id) {
                DB::table('variants')
                  ->where('id', $cart->variants_id)
                  ->decrement('stock', $cart->qty);
            } else {
                $item->decrement('qty', $cart->qty);
            }
        }
    }

    /**
     * Check if order can be cancelled
     */
    private function canCancelOrder($order)
    {
        // Allow cancellation within 15 minutes of order
        $cutoff = Carbon::parse($order->created_at)->addMinutes(15);
        return Carbon::now()->lt($cutoff) && in_array($order->status_type, [1, 2]);
    }

    /**
     * Restore stock on cancellation
     */
    private function restoreStock($order)
    {
        $orderDetails = OrderDetails::where('order_id', $order->id)->get();

        foreach ($orderDetails as $detail) {
            $item = Item::find($detail->item_id);
            
            if ($item && $item->stock_management == 1) {
                if ($detail->variants_id) {
                    DB::table('variants')
                      ->where('id', $detail->variants_id)
                      ->increment('stock', $detail->qty);
                } else {
                    $item->increment('qty', $detail->qty);
                }
            }
        }
    }
}