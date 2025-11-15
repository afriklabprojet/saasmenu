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
use App\Models\Coupons;
use App\Models\DeliveryArea;
use App\Models\TableQR;
use App\Models\User;
use App\Models\Settings;
use App\Models\Variants;
use App\Models\Timing;
use App\Helpers\helper;
use App\Services\AuditService;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use DateTime;
use DateInterval;
use App\Http\Controllers\web\Traits\VendorDataTrait;

class OrderController extends Controller
{
    use VendorDataTrait;
    /**
     * Display checkout page
     */
    public function checkout(Request $request, $slug = null)
    {
        // Resolve vendor from slug parameter, host, or session
        $vdata = null;
        if ($slug) {
            $restaurant = \App\Models\Restaurant::where('restaurant_slug', $slug)->first();
            $vdata = $restaurant ? $restaurant->user_id : null;
        }
        $vdata = $vdata ?: $this->getVendorId($request) ?: Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['error' => 'Restaurant non sélectionné'], 400);
        }

        $settingdata = helper::appdata($vdata);

        // cart items for this vendor
        $cartdata = $this->getCartItems($vdata);

        if ($cartdata->isEmpty()) {
            return redirect('/cart')->with('error', 'Votre panier est vide');
        }

        // Validate stock and min/max order before checkout
        try {
            $this->validateCartStock($cartdata, $vdata);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        // Payment methods, coupons, delivery areas, and table QR
        $paymentmethods = Payment::where('vendor_id', $vdata)
                                ->where('is_available', 1)
                                ->get();

        $coupons = Coupons::where('vendor_id', $vdata)
                          ->where('is_available', 1)
                          ->get();

        $deliveryAreas = DeliveryArea::where('vendor_id', $vdata)
                                     ->where('is_available', 1)
                                     ->get();

        $tableQrs = TableQR::where('vendor_id', $vdata)
                           ->where('is_available', 1)
                           ->get();

        // Prepare tax aggregation per item (if needed by view)
        $taxArr = [];
        foreach ($cartdata as $c) {
            // Use existing helper to fetch tax definition when available
            if (isset($c->tax)) {
                $taxArr[$c->item_id] = helper::gettax($c->tax);
            } else {
                $taxArr[$c->item_id] = [];
            }
        }

        return view('front.checkout', compact('settingdata', 'cartdata', 'vdata', 'paymentmethods', 'coupons', 'deliveryAreas', 'tableQrs', 'taxArr'));
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
        $orderNumber = $request->route('order_number') ?: $request->order_number;

        // Resolve vendor and load store info
        $vdata = $this->getVendorId($request) ?: Session::get('restaurant_id');
        $storeinfo = $this->getStoreInfo($request);

        $order = Order::where('order_number', $orderNumber)
                     ->where('vendor_id', $vdata)
                     ->with('orderdetails.item')
                     ->first();

        if (!$order) {
            return redirect('/')->with('error', 'Commande introuvable');
        }

        // Generate WhatsApp message
        $whmessage = helper::whatsappmessage($orderNumber, $vdata, $storeinfo);

        $settingdata = helper::appdata($vdata);

        // Audit log
        AuditService::logAdminAction(
            'VIEW_ORDER_SUCCESS',
            'Order',
            ['order_number' => $orderNumber],
            $order->id
        );

        return view('front.ordersuccess', compact('order', 'settingdata', 'vdata', 'storeinfo', 'orderNumber', 'whmessage'));
    }

    /**
     * Track order
     */
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:50',
        ]);

        $vdata = $this->getVendorId($request) ?: Session::get('restaurant_id');
        $storeinfo = $this->getStoreInfo($request);

        $status = Order::selectRaw('order_number, DATE_FORMAT(created_at, "%d %M %Y") as date, address, building, landmark, pincode, order_type, id, discount_amount, status, status_type, order_notes, tax, tax_name, delivery_charge, couponcode, offer_type, sub_total, grand_total, customer_name, customer_email, mobile')
            ->where('order_number', $request->order_number ?: $request->ordernumber)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$status) {
            return response()->json(['status' => 0, 'message' => 'Commande introuvable'], 404);
        }

        // Load order with tableqr relation
        $orderdata = Order::with('tableqr')
                          ->where('order_number', $request->order_number ?: $request->ordernumber)
                          ->where('vendor_id', $vdata)
                          ->first();

        $orderdetails = OrderDetails::where('order_id', $status->id)->get();

        // Build summary array for view
        $summery = [
            'id' => $status->id,
            'tax' => $status->tax,
            'tax_name' => $status->tax_name,
            'discount_amount' => $status->discount_amount,
            'order_number' => $status->order_number,
            'created_at' => $status->date,
            'delivery_charge' => $status->delivery_charge,
            'address' => $status->address,
            'building' => $status->building,
            'landmark' => $status->landmark,
            'pincode' => $status->pincode,
            'order_notes' => $status->order_notes,
            'status' => $status->status,
            'status_type' => $status->status_type,
            'order_type' => $status->order_type,
            'couponcode' => $status->couponcode,
            'offer_type' => $status->offer_type,
            'sub_total' => $status->sub_total,
            'grand_total' => $status->grand_total,
            'customer_name' => $status->customer_name,
            'customer_email' => $status->customer_email,
            'mobile' => $status->mobile,
        ];

        // Check if it's a view request (return view) or API request (return JSON)
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'status' => 1,
                'order' => $status,
                'orderdata' => $orderdata,
                'order_details' => $orderdetails,
                'summery' => $summery
            ]);
        }

        return view('front.track-order', compact('vdata', 'storeinfo', 'orderdata', 'summery', 'orderdetails'));
    }

    /**
     * Apply promo code
     */
    public function applyPromocode(Request $request)
    {
        if ($request->promocode == "") {
            return response()->json(["status" => 0, "message" => trans('messages.enter_promocode')], 200);
        }

        $promocode = Coupons::where('code', $request->promocode)
                            ->where('vendor_id', $request->vendor_id)
                            ->first();

        if (!$promocode) {
            return response()->json(['status' => 0, 'message' => trans('messages.wrong_promocode')], 200);
        }

        // Set timezone if defined
        if (@helper::appdata($request->vendor_id)->timezone != "") {
            date_default_timezone_set(helper::appdata($request->vendor_id)->timezone);
        }

        $current_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime($promocode->active_from));
        $end_date = date('Y-m-d', strtotime($promocode->active_to));

        if ($start_date <= $current_date && $current_date <= $end_date) {
            if ($promocode->limit > 0) {
                if ($request->sub_total < @$promocode->price) {
                    return response()->json(["status" => 0, "message" => trans('messages.not_eligible')], 200);
                }
                session([
                    'offer_amount' => @$promocode->price,
                    'offer_code' => @$promocode->code,
                    'offer_type' => 'promocode',
                ]);

                // Audit log
                AuditService::logAdminAction(
                    'APPLY_PROMOCODE',
                    'Coupons',
                    [
                        'code' => $promocode->code,
                        'amount' => $promocode->price,
                        'vendor_id' => $request->vendor_id
                    ],
                    $promocode->id
                );

                return response()->json(['status' => 1, 'message' => trans('messages.promocode_applied'), 'data' => $promocode], 200);
            } else {
                return response()->json(['status' => 0, 'message' => trans('messages.limit_over')], 200);
            }
        } else {
            return response()->json(['status' => 0, 'message' => trans('messages.promocode_expired')], 200);
        }
    }

    /**
     * Remove promo code
     */
    public function removePromocode(Request $request)
    {
        session()->forget(['offer_amount', 'offer_code', 'offer_type']);

        // Audit log
        AuditService::logAdminAction(
            'REMOVE_PROMOCODE',
            'Coupons',
            ['vendor_id' => $request->vendor_id ?? Session::get('restaurant_id')]
        );

        return response()->json(['status' => 1, 'message' => trans('messages.promocode_removed')], 200);
    }

    /**
     * Get available time slots for delivery/pickup
     */
    public function timeslot(Request $request)
    {
        try {
            $vdata = $this->getVendorId($request) ?: $request->vendor_id;

            $timezone = helper::appdata($vdata);
            $slots = [];

            date_default_timezone_set($timezone->timezone);

            if ($request->inputDate != "" && $request->inputDate != null) {
                $day = date('l', strtotime($request->inputDate));

                $time = Timing::where('vendor_id', $vdata)
                              ->where('day', $day)
                              ->first();

                if (!$time) {
                    return response()->json(['status' => 0, 'message' => trans('messages.no_timing_available')], 200);
                }

                if ($time->is_always_close == 1) {
                    $slots = "1"; // Restaurant closed
                } else {
                    // Calculate slot duration in minutes
                    $minute = "";
                    if (helper::appdata($vdata)->interval_type == 2) {
                        $minute = (float)helper::appdata($vdata)->interval_time * 60;
                    }
                    if (helper::appdata($vdata)->interval_type == 1) {
                        $minute = helper::appdata($vdata)->interval_time;
                    }

                    $duration = $minute;
                    $cleanup = 0;
                    $start = $time->open_time;
                    $break_start = $time->break_start;
                    $break_end = $time->break_end;
                    $end = $time->close_time;

                    $firsthalf = $this->firsthalf($duration, $cleanup, $start, $break_start);
                    $secondhalf = $this->secondhalf($duration, $cleanup, $break_end, $end);
                    $period = array_merge($firsthalf, $secondhalf);

                    $currenttime = Carbon::now()->format('h:i a');
                    $current_date = Carbon::now()->format('Y-m-d');

                    foreach ($period as $item) {
                        if ($request->inputDate == $current_date) {
                            $slottime = explode('-', $item);
                            if (strtotime($slottime[0]) <= strtotime($currenttime)) {
                                $status = "";
                            } else {
                                $status = "active";
                            }
                        } else {
                            $status = "active";
                        }
                        $slots[] = array(
                            'slot' => $item,
                            'status' => $status,
                        );
                    }
                }
            }
            return $slots;
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'message' => trans('messages.wrong')], 200);
        }
    }

    /**
     * Generate time slots for first half (before break)
     */
    private function firsthalf($duration, $cleanup, $start, $break_start)
    {
        $start = new DateTime($start);
        $break_start = new DateTime($break_start);
        $interval = new DateInterval('PT' . $duration . 'M');
        $cleanupinterval = new DateInterval('PT' . $cleanup . 'M');
        $slots = array();

        for ($intStart = $start; $intStart < $break_start; $intStart->add($interval)->add($cleanupinterval)) {
            $endperiod = clone $intStart;
            $endperiod->add($interval);
            if (strtotime($break_start->format('h:i A')) < strtotime($endperiod->format('h:i A')) && strtotime($endperiod->format('h:i A')) < strtotime($break_start->format('h:i A'))) {
                $endperiod = $break_start;
                $slots[] = $intStart->format('h:i A') . ' - ' . $endperiod->format('h:i A');
                $intStart = $break_start;
                $endperiod = $break_start;
                $intStart->sub($interval);
            }
            $slots[] = $intStart->format('h:i A') . ' - ' . $endperiod->format('h:i A');
        }
        return $slots;
    }

    /**
     * Generate time slots for second half (after break)
     */
    private function secondhalf($duration, $cleanup, $break_end, $end)
    {
        $break_end = new DateTime($break_end);
        $end = new DateTime($end);
        $interval = new DateInterval('PT' . $duration . 'M');
        $cleanupinterval = new DateInterval('PT' . $cleanup . 'M');
        $slots = array();

        for ($intStart = $break_end; $intStart < $end; $intStart->add($interval)->add($cleanupinterval)) {
            $endperiod = clone $intStart;
            $endperiod->add($interval);
            if (strtotime($end->format('h:i A')) < strtotime($endperiod->format('h:i A')) && strtotime($endperiod->format('h:i A')) < strtotime($break_end->format('h:i A'))) {
                $endperiod = $end;
                $slots[] = $intStart->format('h:i A') . ' - ' . $endperiod->format('h:i A');
                $intStart = $end;
                $endperiod = $end;
                $intStart->sub($interval);
            }
            $slots[] = $intStart->format('h:i A') . ' - ' . $endperiod->format('h:i A');
        }
        return $slots;
    }

    /**
     * Handle payment gateway callbacks and create order
     * Called after payment gateway redirects back (PayTab, Mollie, Xendit, etc.)
     */
    public function ordercreate(Request $request)
    {
        $paymentid = "";

        // Extract payment ID from different gateway responses
        if ($request->paymentId != "") {
            $paymentid = $request->paymentId;
        }
        if ($request->payment_id != "") {
            $paymentid = $request->payment_id;
        }
        if ($request->transaction_id != "") {
            $paymentid = $request->transaction_id;
        }

        // PhonePe callback (payment_type 11)
        if (Session::get('payment_type') == "11") {
            if ($request->code == "PAYMENT_SUCCESS") {
                $paymentid = $request->transactionId;
            }
        }

        // PayTab callback (payment_type 12)
        if (Session::get('payment_type') == "12") {
            $checkstatus = app('App\Http\Controllers\addons\PayTabController')
                            ->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));

            if ($checkstatus == "A") {
                $paymentid = Session::get('tran_ref');
            } else {
                return redirect(Session::get('failureurl'))
                         ->with('error', Session::get('paytab_response'));
            }
        }

        // Mollie callback (payment_type 13)
        if (Session::get('payment_type') == "13") {
            $checkstatus = app('App\Http\Controllers\addons\MollieController')
                            ->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));

            if ($checkstatus == "A") {
                $paymentid = Session::get('tran_ref');
            } else {
                return redirect(Session::get('failureurl'))
                         ->with('error', Session::get('mollie_response'));
            }
        }

        // Khalti callback (payment_type 14)
        if (Session::get('payment_type') == "14") {
            if ($request->status == "Completed") {
                $paymentid = $request->transaction_id;
            } else {
                return redirect(Session::get('failureurl'))
                         ->with('error', 'Payment failed');
            }
        }

        // Xendit callback (payment_type 15)
        if (Session::get('payment_type') == "15") {
            $checkstatus = app('App\Http\Controllers\addons\XenditController')
                            ->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));

            if ($checkstatus == "PAID") {
                $paymentid = Session::get('payment_id');
            } else {
                return redirect(Session::get('failureurl'))
                         ->with('error', 'Payment not completed');
            }
        }

        // Get user/session
        $user_id = Auth::check() && Auth::user()->type == 3 ? Auth::user()->id : null;
        $session_id = $user_id ? null : Session::getId();

        // Create order using helper
        $orderresponse = helper::createorder(
            Session::get('vendor_id'),
            $user_id,
            $session_id,
            Session::get('payment_type'),
            $paymentid,
            Session::get('customer_email'),
            Session::get('customer_name'),
            Session::get('customer_mobile'),
            Session::get('stripeToken'),
            Session::get('grand_total'),
            Session::get('delivery_charge'),
            Session::get('address'),
            Session::get('building'),
            Session::get('landmark'),
            Session::get('postal_code'),
            Session::get('discount_amount'),
            Session::get('offer_type'),
            Session::get('sub_total'),
            Session::get('tax'),
            Session::get('tax_name'),
            Session::get('delivery_time'),
            Session::get('delivery_date'),
            Session::get('delivery_area'),
            Session::get('couponcode'),
            Session::get('order_type'),
            Session::get('notes'),
            Session::get('table'),
            '',
            Session::get('buynow')
        );

        $slug = Session::get('slug');
        $order_number = $orderresponse;

        // Audit log
        AuditService::logAdminAction(
            'ORDER_CREATED_VIA_GATEWAY',
            'Order',
            [
                'order_number' => $order_number,
                'payment_type' => Session::get('payment_type'),
                'payment_id' => $paymentid
            ]
        );

        return view('front.mercadoorder', compact('slug', 'order_number'));
    }

    /**
     * Process payment method selection and order creation
     * Simplified version - supports COD and delegates to helper::createorder
     */
    public function paymentmethod(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|integer',
            'payment_type' => 'required|integer',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'mobile' => 'required|string|max:20',
            'order_type' => 'required|in:1,2',
            'grand_total' => 'required|numeric|min:0',
        ]);

        // Determine vendor_id and buynow flag
        $vendor_id = $request->payment_type == 6 ? $request->modal_vendor_id : $request->vendor_id;
        $buynow = $request->payment_type == 6 ? $request->modal_buynow : ($request->buynow ?? 0);

        // Get user or session
        $user_id = Auth::check() && Auth::user()->type == 3 ? Auth::user()->id : null;
        $session_id = Auth::check() ? null : Session::getId();

        $storeinfo = helper::storeinfo($request->vendor);

        // Get and validate cart
        $cartitems = Cart::select('carts.id', 'carts.item_id', 'carts.item_name', 'carts.item_price', 'carts.qty', 'carts.price', 'carts.tax', 'carts.variants_id')
            ->where('carts.vendor_id', $vendor_id);

        if ($user_id) {
            $cartitems->where('carts.user_id', $user_id);
        } else {
            $cartitems->where('carts.session_id', $session_id);
        }

        $cartitems->where('carts.buynow', $buynow);
        $cartdata = $cartitems->get();

        if ($cartdata->count() == 0) {
            return response()->json(['status' => 0, 'message' => trans('messages.cart_empty')], 200);
        }

        // Validate stock and min/max order (same as checkout validation)
        try {
            $this->validateCartStock($cartdata, $vendor_id);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 200);
        }

        // Calculate taxes
        $tax_total = 0;
        $tax_name = [];
        $tax_price = [];

        foreach ($cartdata as $cart) {
            $taxlist = helper::gettax($cart->tax);
            if (!empty($taxlist)) {
                foreach ($taxlist as $tax) {
                    if (!empty($tax)) {
                        if (!in_array($tax->name, $tax_name)) {
                            $tax_name[] = $tax->name;
                            if ($tax->type == 1) {
                                $price = $tax->tax * $cart->qty;
                            } else if ($tax->type == 2) {
                                $price = ($tax->tax / 100) * ($cart->price * $cart->qty);
                            } else {
                                $price = 0;
                            }
                            $tax_price[] = $price;
                        } else {
                            $index = array_search($tax->name, $tax_name);
                            if ($tax->type == 1) {
                                $price = $tax->tax * $cart->qty;
                            } else if ($tax->type == 2) {
                                $price = ($tax->tax / 100) * ($cart->price * $cart->qty);
                            } else {
                                $price = 0;
                            }
                            $tax_price[$index] += $price;
                        }
                    }
                }
            }
        }

        $tax_total = array_sum($tax_price);

        // Prepare order data
        $payment_id = $request->payment_id ?? "";
        $filename = "";

        // Handle payment type 6 (Bank transfer with screenshot)
        if ($request->payment_type == '6') {
            if ($request->hasFile('screenshot')) {
                $filename = 'screenshot-' . uniqid() . "." . $request->file('screenshot')->getClientOriginalExtension();
                $request->file('screenshot')->move(env('ASSETPATHURL') . 'admin-assets/images/screenshot/', $filename);
            }

            // Use modal_ prefixed fields for payment type 6
            $orderresponse = helper::createorder(
                $request->modal_vendor_id,
                $user_id,
                $session_id,
                $request->payment_type,
                $payment_id,
                $request->modal_customer_email,
                $request->modal_customer_name,
                $request->modal_customer_mobile,
                $request->stripeToken ?? '',
                $request->modal_grand_total,
                $request->modal_delivery_charge ?? 0,
                $request->modal_address ?? '',
                $request->modal_building ?? '',
                $request->modal_landmark ?? '',
                $request->modal_postal_code ?? '',
                $request->modal_discount_amount ?? 0,
                $request->modal_offer_type ?? '',
                $request->modal_subtotal,
                $tax_total,
                implode("|", $tax_name),
                $request->modal_delivery_time ?? '',
                $request->modal_delivery_date ?? '',
                $request->modal_delivery_area ?? '',
                $request->modal_couponcode ?? '',
                $request->modal_order_type,
                $request->modal_notes ?? '',
                $request->modal_table ?? '',
                $filename,
                $buynow
            );
        } else {
            // Standard order creation
            $orderresponse = helper::createorder(
                $request->vendor_id,
                $user_id,
                $session_id,
                $request->payment_type,
                $payment_id,
                $request->customer_email,
                $request->customer_name,
                $request->mobile,
                $request->stripeToken ?? '',
                $request->grand_total,
                $request->delivery_charge ?? 0,
                $request->address ?? '',
                $request->building ?? '',
                $request->landmark ?? '',
                $request->postal_code ?? '',
                $request->discount_amount ?? 0,
                $request->offer_type ?? '',
                $request->sub_total,
                $tax_total,
                implode("|", $tax_name),
                $request->delivery_time ?? '',
                $request->delivery_date ?? '',
                $request->delivery_area ?? '',
                $request->couponcode ?? '',
                $request->order_type,
                $request->notes ?? '',
                $request->table ?? '',
                $filename,
                $buynow
            );
        }

        // Handle response
        if ($orderresponse == -1) {
            return response()->json(['status' => 0, 'message' => trans('messages.cart_empty')], 200);
        }

        if ($orderresponse == "false") {
            return response()->json(['status' => 0, 'message' => trans('messages.order_not_placed')], 200);
        }

        // Decrement coupon limit if used
        if ($request->offer_type == "promocode" && $request->couponcode != null) {
            $promocode = Coupons::where('code', $request->couponcode)
                                ->where('vendor_id', $vendor_id)
                                ->first();
            if ($promocode) {
                $promocode->decrement('limit', 1);
            }
        }

        // Audit log
        AuditService::logAdminAction(
            'CREATE_ORDER_VIA_PAYMENT',
            'Order',
            [
                'order_number' => $orderresponse,
                'payment_type' => $request->payment_type,
                'grand_total' => $request->grand_total ?? $request->modal_grand_total
            ]
        );

        // Return success response
        if ($request->payment_type == '6') {
            return redirect($request->slug . '/success/' . $orderresponse)
                         ->with('success', trans('messages.order_placed'));
        } else {
            $url = url($request->slug . "/success/" . $orderresponse);
            return response()->json([
                'status' => 1,
                'message' => trans('messages.order_placed'),
                'order_number' => $orderresponse,
                'url' => $url
            ], 200);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $orderNumber)
    {
        $vdata = $this->getVendorId($request) ?: Session::get('restaurant_id');
        $storeinfo = $this->getStoreInfo($request);

        $orderdata = Order::where('order_number', $orderNumber ?: $request->ordernumber)
                          ->where('vendor_id', $vdata)
                          ->first();

        if (!$orderdata) {
            return redirect()->back()->with('error', trans('messages.order_not_found'));
        }

        // Check current status
        if ($orderdata->status_type == 2) {
            return redirect()->back()->with('error', trans('messages.already_accepted'));
        } else if ($orderdata->status_type == 4) {
            return redirect()->back()->with('error', trans('messages.already_rejected'));
        } else if ($orderdata->status_type == 3) {
            return redirect()->back()->with('error', trans('messages.already_delivered'));
        }

        // Get cancelled status from CustomStatus
        $defaultsatus = CustomStatus::where('vendor_id', $storeinfo->id)
                                    ->where('order_type', $orderdata->order_type)
                                    ->where('type', 4)
                                    ->where('is_available', 1)
                                    ->where('is_deleted', 2)
                                    ->first();

        if (empty($defaultsatus)) {
            return redirect()->back()->with('error', trans('messages.wrong'));
        }

        try {
            DB::beginTransaction();

            // Update order status
            $orderdata->status_type = $defaultsatus->type;
            $orderdata->status = $defaultsatus->id;
            $orderdata->update();

            // Restore stock
            $orderdetails = OrderDetails::where('order_id', $orderdata->id)->get();
            foreach ($orderdetails as $order) {
                if ($order->variants_id != null && $order->variants_id != "") {
                    $item = Variants::where('id', $order->variants_id)
                                   ->where('item_id', $order->item_id)
                                   ->first();
                } else {
                    $item = Item::where('id', $order->item_id)
                               ->where('vendor_id', $storeinfo->id)
                               ->first();
                }
                if ($item) {
                    $item->qty = $item->qty + $order->qty;
                    $item->update();
                }
            }

            // Get status title
            $title = helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type, $storeinfo->id)->name;
            $message_text = 'Order ' . $orderdata->order_number . ' has been cancelled by ' . ($orderdata->customer_name ?? 'customer');

            // Email configuration and send
            $emaildata = helper::emailconfigration($storeinfo->id);
            Config::set('mail', $emaildata);
            helper::cancel_order($storeinfo->email, $storeinfo->name, $title, $message_text, $orderdata);

            // Push notification to vendor
            $vendorData = User::select('id', 'name', 'slug', 'email', 'mobile', 'token')
                             ->where('id', $orderdata->vendor_id)
                             ->first();
            if ($vendorData && $vendorData->token) {
                $body = "#" . $orderNumber . " has been cancelled";
                helper::push_notification($vendorData->token, $title, $body, "order", $orderdata->id);
            }

            // Audit log
            AuditService::logAdminAction(
                'CANCEL_ORDER',
                'Order',
                [
                    'reason' => 'Customer cancellation',
                    'title' => $title,
                    'notification_sent' => true,
                    'email_sent' => true
                ],
                $orderdata->id
            );

            DB::commit();

            return redirect()->back()->with('success', trans('messages.success'));

        } catch (\Exception $e) {
            DB::rollBack();

            AuditService::logSecurityEvent('ORDER_CANCELLATION_FAILED', [
                'error' => $e->getMessage(),
                'order_number' => $orderNumber
            ]);

            return redirect()->back()->with('error', trans('messages.wrong'));
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
        $deliveryCharge = $this->calculateDeliveryCharge($request->order_type, $vendorId, $request->delivery_area ?? null);
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
     * Calculate tax aggregated from cart items
     */
    private function calculateTax($subtotal, $vendorId)
    {
        $cartItems = $this->getCartItems($vendorId);

        $tax_total = 0;
        $tax_name = [];
        $tax_price = [];

        foreach ($cartItems as $cart) {
            $taxlist = helper::gettax($cart->tax);

            if (!empty($taxlist)) {
                foreach ($taxlist as $tax) {
                    if (!empty($tax)) {
                        $producttax = helper::taxRate($tax->tax, $cart->price, $cart->qty, $tax->type);

                        // Aggregate by tax name
                        if (!in_array($tax->name, $tax_name)) {
                            $tax_name[] = $tax->name;

                            // Calculate price based on type (1=fixed, 2=percentage)
                            if ($tax->type == 1) {
                                $price = $tax->tax * $cart->qty;
                            } else if ($tax->type == 2) {
                                $price = ($tax->tax / 100) * ($cart->price * $cart->qty);
                            } else {
                                $price = 0;
                            }
                            $tax_price[] = $price;
                        } else {
                            // Add to existing tax
                            $index = array_search($tax->name, $tax_name);
                            if ($tax->type == 1) {
                                $price = $tax->tax * $cart->qty;
                            } else if ($tax->type == 2) {
                                $price = ($tax->tax / 100) * ($cart->price * $cart->qty);
                            } else {
                                $price = 0;
                            }
                            $tax_price[$index] += $price;
                        }
                    }
                }
            }
        }

        // Sum all taxes
        $tax_total = array_sum($tax_price);

        return $tax_total;
    }

    /**
     * Calculate delivery charge based on delivery area
     */
    private function calculateDeliveryCharge($orderType, $vendorId, $deliveryAreaId = null)
    {
        if ($orderType == 2) { // Pickup
            return 0;
        }

        // If delivery area ID provided, get its charge
        if ($deliveryAreaId) {
            $deliveryArea = DeliveryArea::where('id', $deliveryAreaId)
                                       ->where('vendor_id', $vendorId)
                                       ->where('is_available', 1)
                                       ->first();

            if ($deliveryArea) {
                return $deliveryArea->delivery_charge ?? 0;
            }
        }

        // Fallback: get default or first delivery area
        $defaultArea = DeliveryArea::where('vendor_id', $vendorId)
                                   ->where('is_available', 1)
                                   ->orderBy('id', 'asc')
                                   ->first();

        return $defaultArea ? ($defaultArea->delivery_charge ?? 0) : 0;
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
     * Validate cart stock and min/max order constraints
     *
     * @throws \Exception if validation fails
     */
    private function validateCartStock($cartdata, $vdata)
    {
        foreach ($cartdata as $cart) {
            if ($cart->variants_id != "" && $cart->variants_id != null) {
                // Aggregate cart qty for this variant
                if (Auth::check()) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                                ->where('variants_id', $cart->variants_id)
                                ->where('user_id', Auth::id())
                                ->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                                ->where('variants_id', $cart->variants_id)
                                ->where('session_id', Session::getId())
                                ->first();
                }

                $variant = Variants::where('id', $cart->variants_id)->first();
                $item_name = Item::select('item_name')->where('id', $cart->item_id)->first();

                if ($variant && $variant->stock_management == 1) {
                    // Min order validation
                    if ($variant->min_order != null && $variant->min_order != "" && $variant->min_order != 0) {
                        if ($cartqty->totalqty < $variant->min_order) {
                            throw new \Exception(trans('messages.min_qty_message') . $variant->min_order . " " . ($item_name->item_name));
                        }
                    }

                    // Max order validation
                    if ($variant->max_order != null && $variant->max_order != "" && $variant->max_order != 0) {
                        if ($variant->max_order < $cartqty->totalqty) {
                            throw new \Exception(trans('messages.max_qty_message') . $variant->max_order . ' ' . ($item_name->item_name));
                        }
                    }

                    // Stock validation
                    if ($cart->qty > $variant->qty) {
                        throw new \Exception(trans('messages.cart_qty_msg') . ' ' . trans('labels.out_of_stock_msg') . ' ' . $item_name->item_name . '(' . $variant->name . ')');
                    }
                }
            } else {
                // Items without variants
                $item = Item::where('id', $cart->item_id)->first();

                if (Auth::check()) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                                ->where('item_id', $cart->item_id)
                                ->where('user_id', Auth::id())
                                ->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                                ->where('item_id', $cart->item_id)
                                ->where('session_id', Session::getId())
                                ->first();
                }

                if ($item && $item->stock_management == 1) {
                    if ($item->min_order != null && $item->min_order != "" && $item->min_order != 0) {
                        if ($cartqty->totalqty < $item->min_order) {
                            throw new \Exception(trans('messages.min_qty_message') . $item->min_order . ' ' . ($item->item_name));
                        }
                    }

                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($item->max_order < $cartqty->totalqty) {
                            throw new \Exception(trans('messages.max_qty_message') . $item->max_order . ' ' . ($item->item_name));
                        }
                    }

                    if ($cart->qty > $item->qty) {
                        throw new \Exception(trans('messages.cart_qty_msg') . ' ' . trans('labels.out_of_stock_msg') . ' ' . $item->item_name);
                    }
                }
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
