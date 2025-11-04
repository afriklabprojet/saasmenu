<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Variants;
use App\Helpers\helper;
use App\Services\AuditService;

class CartController extends Controller
{
    /**
     * Add item to cart
     */
    public function addToCart(Request $request)
    {
        // Validation
        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'qty' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'variation_id' => 'nullable|integer|exists:variants,id',
        ]);

        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        $item = Item::where('id', $request->item_id)
                   ->where('vendor_id', $vdata)
                   ->where('is_available', 1)
                   ->first();

        if (!$item) {
            return response()->json(['status' => 0, 'message' => 'Produit non disponible'], 404);
        }

        // Check stock availability
        if ($request->variation_id) {
            $variation = Variants::find($request->variation_id);
            if (!$variation || $variation->stock < $request->qty) {
                return response()->json(['status' => 0, 'message' => 'Stock insuffisant'], 400);
            }
        } elseif ($item->stock_management == 1 && $item->qty < $request->qty) {
            return response()->json(['status' => 0, 'message' => 'Stock insuffisant'], 400);
        }

        // Get existing cart quantity
        $existingQty = $this->getExistingCartQuantity($request);
        $totalQty = $existingQty + $request->qty;

        // Validate total quantity
        if ($request->variation_id) {
            $maxQty = $variation->stock ?? 0;
        } else {
            $maxQty = $item->qty ?? 0;
        }

        if ($item->stock_management == 1 && $totalQty > $maxQty) {
            return response()->json([
                'status' => 0,
                'message' => 'Quantité totale dépassée. Maximum: ' . $maxQty
            ], 400);
        }

        // Create cart item
        $cart = $this->createCartItem($request, $item, $vdata);

        // Log cart activity
        AuditService::logAdminAction(
            'ADD_TO_CART',
            'Cart',
            [
                'item_id' => $request->item_id,
                'qty' => $request->qty,
                'variation_id' => $request->variation_id
            ],
            $cart->id
        );

        return response()->json([
            'status' => 1,
            'message' => 'Produit ajouté au panier',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Display cart contents
     */
    public function cart(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        $cartdata = $this->getCartItems($vdata);

        return view('front.cart', compact('settingdata', 'cartdata', 'vdata'));
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = Cart::find($request->cart_id);

        if (!$cart) {
            return response()->json(['status' => 0, 'message' => 'Article de panier introuvable'], 404);
        }

        // Verify ownership
        if (!$this->verifyCartOwnership($cart)) {
            return response()->json(['status' => 0, 'message' => 'Non autorisé'], 403);
        }

        // Validate stock
        $stockCheck = $this->validateStock($cart, $request->qty);
        if (!$stockCheck['valid']) {
            return response()->json(['status' => 0, 'message' => $stockCheck['message']], 400);
        }

        $cart->qty = $request->qty;
        $cart->save();

        return response()->json([
            'status' => 1,
            'message' => 'Quantité mise à jour',
            'total' => $this->calculateCartTotal()
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
        ]);

        $cart = Cart::find($request->cart_id);

        if (!$cart || !$this->verifyCartOwnership($cart)) {
            return response()->json(['status' => 0, 'message' => 'Non autorisé'], 403);
        }

        $cart->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Article supprimé du panier',
            'cart_count' => $this->getCartCount()
        ]);
    }

    /**
     * Get cart items for a vendor
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
     * Get existing cart quantity for item
     */
    private function getExistingCartQuantity(Request $request)
    {
        $query = Cart::where('buynow', 0);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', Session::getId());
        }

        if ($request->variation_id) {
            $query->where('variants_id', $request->variation_id);
        } else {
            $query->where('item_id', $request->item_id);
        }

        return $query->sum('qty') ?: 0;
    }

    /**
     * Create cart item
     */
    private function createCartItem(Request $request, $item, $vendorId)
    {
        $cart = new Cart();
        $cart->item_id = $request->item_id;
        $cart->user_id = Auth::id();
        $cart->session_id = Session::getId();
        $cart->vendor_id = $vendorId;
        $cart->qty = $request->qty;
        $cart->price = $request->price;
        $cart->variants_id = $request->variation_id;
        $cart->extras_id = $request->extras_id ?? '';
        $cart->extras_name = $request->extras_name ?? '';
        $cart->extras_price = $request->extras_price ?? 0;
        $cart->buynow = 0;
        $cart->save();

        return $cart;
    }

    /**
     * Verify cart ownership
     */
    private function verifyCartOwnership($cart)
    {
        if (Auth::check()) {
            return $cart->user_id == Auth::id();
        } else {
            return $cart->session_id == Session::getId();
        }
    }

    /**
     * Validate stock for cart item
     */
    private function validateStock($cart, $newQty)
    {
        $item = $cart->item;

        if (!$item || $item->stock_management != 1) {
            return ['valid' => true];
        }

        if ($cart->variants_id) {
            $variant = $cart->variants;
            $availableStock = $variant ? $variant->stock : 0;
        } else {
            $availableStock = $item->qty;
        }

        if ($newQty > $availableStock) {
            return [
                'valid' => false,
                'message' => "Stock insuffisant. Disponible: {$availableStock}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get cart items count
     */
    private function getCartCount()
    {
        $query = Cart::where('buynow', 0);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', Session::getId());
        }

        return $query->sum('qty') ?: 0;
    }

    /**
     * Calculate cart total
     */
    private function calculateCartTotal()
    {
        $cartItems = $this->getCartItems(Session::get('restaurant_id'));
        $total = 0;

        foreach ($cartItems as $cart) {
            $itemTotal = $cart->price * $cart->qty;
            $itemTotal += $cart->extras_price * $cart->qty;
            $total += $itemTotal;
        }

        return $total;
    }
}
