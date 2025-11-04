<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Coupons;
use App\Models\Cart;
use App\Services\AuditService;
use Carbon\Carbon;

class PromoCodeController extends Controller
{
    /**
     * Apply promo code to cart
     */
    public function apply(Request $request)
    {
        $request->validate([
            'promocode' => 'required|string|max:50',
        ]);

        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        // Check if cart has items
        $cartItems = $this->getCartItems($vdata);
        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'Votre panier est vide'], 400);
        }

        // Find promo code
        $coupon = Coupons::where('name', $request->promocode)
                         ->where('vendor_id', $vdata)
                         ->where('is_available', 1)
                         ->first();

        if (!$coupon) {
            return response()->json(['status' => 0, 'message' => 'Code promo invalide'], 404);
        }

        // Validate coupon
        $validation = $this->validateCoupon($coupon, $cartItems);
        if (!$validation['valid']) {
            return response()->json(['status' => 0, 'message' => $validation['message']], 400);
        }

        // Calculate discount
        $discount = $this->calculateDiscount($coupon, $cartItems);

        // Store in session
        Session::put('applied_coupon', [
            'code' => $coupon->name,
            'type' => $coupon->type, // 1=fixed, 2=percentage
            'value' => $coupon->price,
            'discount_amount' => $discount['amount'],
            'coupon_id' => $coupon->id
        ]);

        // Log promo code usage
        AuditService::logAdminAction(
            'APPLY_PROMOCODE',
            'Coupon',
            [
                'code' => $coupon->name,
                'discount_amount' => $discount['amount'],
                'cart_total' => $discount['cart_total']
            ],
            $coupon->id
        );

        return response()->json([
            'status' => 1,
            'message' => 'Code promo appliqué avec succès',
            'discount_amount' => $discount['amount'],
            'cart_total' => $discount['cart_total'],
            'final_total' => $discount['final_total']
        ]);
    }

    /**
     * Remove applied promo code
     */
    public function remove(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        $appliedCoupon = Session::get('applied_coupon');

        if (!$appliedCoupon) {
            return response()->json(['status' => 0, 'message' => 'Aucun code promo appliqué'], 400);
        }

        // Remove from session
        Session::forget('applied_coupon');

        // Log removal
        AuditService::logAdminAction(
            'REMOVE_PROMOCODE',
            'Coupon',
            ['code' => $appliedCoupon['code']],
            $appliedCoupon['coupon_id']
        );

        // Recalculate totals
        $cartItems = $this->getCartItems($vdata);
        $cartTotal = $this->calculateCartTotal($cartItems);

        return response()->json([
            'status' => 1,
            'message' => 'Code promo retiré',
            'cart_total' => $cartTotal,
            'final_total' => $cartTotal
        ]);
    }

    /**
     * Get available promo codes for vendor
     */
    public function getAvailable(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        $coupons = Coupons::where('vendor_id', $vdata)
                          ->where('is_available', 1)
                          ->where('start_date', '<=', Carbon::now())
                          ->where('end_date', '>=', Carbon::now())
                          ->select(['name', 'description', 'type', 'price', 'minimum_amount'])
                          ->get();

        return response()->json([
            'status' => 1,
            'coupons' => $coupons
        ]);
    }

    /**
     * Validate coupon eligibility
     */
    private function validateCoupon($coupon, $cartItems)
    {
        // Check date validity
        $now = Carbon::now();
        if ($now->lt(Carbon::parse($coupon->start_date))) {
            return ['valid' => false, 'message' => 'Ce code promo n\'est pas encore actif'];
        }

        if ($now->gt(Carbon::parse($coupon->end_date))) {
            return ['valid' => false, 'message' => 'Ce code promo a expiré'];
        }

        // Check minimum amount
        $cartTotal = $this->calculateCartTotal($cartItems);
        if ($cartTotal < $coupon->minimum_amount) {
            return [
                'valid' => false, 
                'message' => "Montant minimum requis: " . number_format($coupon->minimum_amount, 2) . "€"
            ];
        }

        // Check usage limit
        if ($coupon->usage_limit > 0) {
            $usageCount = $this->getCouponUsageCount($coupon->id);
            if ($usageCount >= $coupon->usage_limit) {
                return ['valid' => false, 'message' => 'Limite d\'utilisation atteinte pour ce code'];
            }
        }

        // Check if already applied
        $appliedCoupon = Session::get('applied_coupon');
        if ($appliedCoupon && $appliedCoupon['coupon_id'] == $coupon->id) {
            return ['valid' => false, 'message' => 'Ce code promo est déjà appliqué'];
        }

        return ['valid' => true];
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscount($coupon, $cartItems)
    {
        $cartTotal = $this->calculateCartTotal($cartItems);
        $discountAmount = 0;

        if ($coupon->type == 1) {
            // Fixed amount discount
            $discountAmount = min($coupon->price, $cartTotal);
        } elseif ($coupon->type == 2) {
            // Percentage discount
            $discountAmount = ($cartTotal * $coupon->price) / 100;
            
            // Apply maximum discount if set
            if ($coupon->maximum_discount > 0) {
                $discountAmount = min($discountAmount, $coupon->maximum_discount);
            }
        }

        $finalTotal = max(0, $cartTotal - $discountAmount);

        return [
            'amount' => $discountAmount,
            'cart_total' => $cartTotal,
            'final_total' => $finalTotal
        ];
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
     * Calculate cart total
     */
    private function calculateCartTotal($cartItems)
    {
        $total = 0;
        foreach ($cartItems as $cart) {
            $total += ($cart->price + $cart->extras_price) * $cart->qty;
        }
        return $total;
    }

    /**
     * Get coupon usage count
     */
    private function getCouponUsageCount($couponId)
    {
        // Count how many times this coupon has been used
        // This would typically be tracked in an order_coupons table
        return 0; // Placeholder - implement based on your tracking method
    }
}