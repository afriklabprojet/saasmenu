<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Variants;
use App\Models\User;
use App\Models\Settings;
use App\Helpers\helper;
use App\Services\AuditService;
use App\Http\Controllers\web\Traits\VendorDataTrait;

/**
 * CartController
 * 
 * Gère toutes les opérations du panier d'achat
 * Version consolidée combinant l'architecture moderne (validation, audit)
 * avec la logique métier complète du HomeController
 * 
 * Refactorisé le: 10 novembre 2025
 */
class CartController extends Controller
{
    use VendorDataTrait;
    /**
     * Add item to cart
     * Enrichi avec logique complète du HomeController (min/max order, buy now, extras)
     */
    public function addToCart(Request $request)
    {
        try {
            // Récupération vendor data
            $vendorData = $this->getVendorData($request);
            $storeinfo = $vendorData['storeinfo'];
            $vdata = $vendorData['vendor_id'];

            // Si c'est un achat immédiat (buy now), supprimer les anciens buy now
            if ($request->buynow == 1) {
                if (Auth::user() && Auth::user()->type == 3) {
                    Cart::where('buynow', 1)->where('user_id', Auth::user()->id)->delete();
                } else {
                    Cart::where('buynow', 1)->where('session_id', Session::getId())->delete();
                }
            }

            // Récupérer item et variation
            $variant_name = str_replace('_', ' ', $request->variants_name ?? '');
            $variation = null;
            
            if ($request->variants_name != null && $request->variants_name != "") {
                $variation = Variants::where('name', str_replace(',', '|', $variant_name))
                    ->where('item_id', $request->item_id)
                    ->first();
            }

            $item = Item::where('id', $request->item_id)->first();

            if (!$item) {
                return response()->json(['status' => 0, 'message' => trans('messages.item_not_found')], 404);
            }

            // Calculer quantité totale dans le panier (existante + nouvelle)
            if ($request->variants_name != null && $request->variants_name != "") {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('variants_id', $variation->id)
                        ->where('user_id', Auth::user()->id)
                        ->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('variants_id', $variation->id)
                        ->where('session_id', Session::getId())
                        ->first();
                }
            } else {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('item_id', $request->item_id)
                        ->where('user_id', Auth::user()->id)
                        ->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('item_id', $request->item_id)
                        ->where('session_id', Session::getId())
                        ->first();
                }
            }

            $qty = ($cartqty->totalqty ?? 0) + $request->qty;

            // Vérifications min/max order et stock
            if ($request->variants_name == null || $request->variants_name == "") {
                // Item sans variante
                if ($item->stock_management == 1) {
                    // Vérification min_order
                    if ($item->min_order != null && $item->min_order != "" && $item->min_order != 0) {
                        if ($qty < $item->min_order) {
                            return response()->json([
                                'status' => 0,
                                'message' => trans('messages.min_qty_message') . $item->min_order
                            ], 200);
                        }
                    }

                    // Vérification max_order
                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($qty > $item->max_order) {
                            $message = ($cartqty->totalqty == null)
                                ? trans('messages.max_qty_message') . $item->max_order
                                : trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $item->max_order;
                            return response()->json(['status' => 0, 'message' => $message], 200);
                        }
                    }

                    // Vérification stock
                    if ($qty > $item->qty) {
                        return response()->json([
                            'status' => 0,
                            'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name
                        ], 200);
                    }
                }
            } else {
                // Item avec variante
                if ($variation && $variation->stock_management == 1) {
                    // Vérification min_order
                    if ($variation->min_order != null && $variation->min_order != "" && $variation->min_order != 0) {
                        if ($qty < $variation->min_order) {
                            return response()->json([
                                'status' => 0,
                                'message' => trans('messages.min_qty_message') . $variation->min_order
                            ], 200);
                        }
                    }

                    // Vérification max_order
                    if ($variation->max_order != null && $variation->max_order != "" && $variation->max_order != 0) {
                        if ($qty > $variation->max_order) {
                            $message = ($cartqty->totalqty == null)
                                ? trans('messages.max_qty_message') . $variation->max_order
                                : trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $variation->max_order;
                            return response()->json(['status' => 0, 'message' => $message], 200);
                        }
                    }

                    // Vérification stock
                    if ($qty > $variation->qty) {
                        return response()->json([
                            'status' => 0,
                            'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name . '(' . $variation->name . ')'
                        ], 200);
                    }
                }
            }

            // Calcul du prix avec extras
            if (!empty($variation)) {
                $cartprice = $variation->price;
                $itemprice = $variation->price;
            } else {
                $cartprice = $request->item_price;
                $itemprice = $request->item_price;
            }

            // Ajouter prix des extras
            $extra_price = explode('|', $request->extras_price ?? '');
            if ($request->extras_price != null && $request->extras_price != "") {
                foreach ($extra_price as $price) {
                    $cartprice = $cartprice + $price;
                }
            }

            // Créer l'item dans le panier
            $cart = new Cart;
            
            if (Auth::user() && Auth::user()->type == 3) {
                $cart->user_id = Auth::user()->id;
            } else {
                $cart->session_id = Session::getId();
            }

            $cart->vendor_id = $request->vendor_id;
            $cart->item_id = $request->item_id;
            $cart->item_name = $request->item_name;
            $cart->item_image = $request->item_image;
            $cart->item_price = $cartprice;
            $cart->tax = $request->tax;
            $cart->extras_name = $request->extras_name;
            $cart->extras_price = $request->extras_price;
            $cart->extras_id = $request->extras_id;
            $cart->qty = $request->qty;
            $cart->price = (float)$cartprice * (float)$request->qty;
            
            if (!empty($variation)) {
                $cart->variants_id = $variation->id;
                $cart->variants_name = str_replace(',', '|', $variant_name);
            }
            
            $cart->variants_price = $itemprice;
            $cart->buynow = $request->buynow ?? 0;
            $cart->save();

            // Mise à jour compteurs et session
            if (Auth::user() && Auth::user()->type == 3) {
                $count = Cart::where('user_id', Auth::user()->id)
                    ->where('vendor_id', $vdata)
                    ->where('buynow', 0)
                    ->count();
                $totalcart = helper::getcartcount($request->vendor_id, Auth::user()->id);
            } else {
                $count = Cart::where('session_id', Session::getId())
                    ->where('vendor_id', $vdata)
                    ->where('buynow', 0)
                    ->count();
                $totalcart = helper::getcartcount($request->vendor_id, '');
            }

            session()->put('cart', $count);
            session()->put('vendor_id', $request->vendor_id);
            session()->put('old_session_id', Session::getId());
            
            $checkouturl = URL::to($storeinfo->slug . '/checkout?buy_now=' . ($request->buynow ?? 0));

            // Log cart activity
            AuditService::logAdminAction(
                'ADD_TO_CART',
                'Cart',
                [
                    'item_id' => $request->item_id,
                    'qty' => $request->qty,
                    'variants_id' => $variation ? $variation->id : null,
                    'buynow' => $request->buynow ?? 0
                ],
                $cart->id
            );

            return response()->json([
                'status' => 1,
                'message' => $request->item_name . ' ' . trans('messages.added_to_cart'),
                'totalcart' => $totalcart,
                'buynow' => $request->buynow ?? 0,
                'checkouturl' => $checkouturl
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display cart contents
     * Enrichi avec calcul taxes détaillé du HomeController
     */
    public function cart(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];

        // Récupérer les items du panier
        $cartitems = Cart::select(
            'id', 'item_id', 'item_name', 'item_image', 'item_price',
            'extras_id', 'extras_name', 'extras_price', 'qty', 'price',
            'tax', 'variants_id', 'variants_name', 'variants_price'
        )->where('vendor_id', $vdata);

        if (Auth::user() && Auth::user()->type == 3) {
            $cartitems->where('user_id', @Auth::user()->id);
        } else {
            $cartitems->where('session_id', Session::getId());
        }

        $cartdata = $cartitems->where('buynow', 0)->get();

        // Calcul des taxes produit par produit
        $itemtaxes = [];
        $producttax = 0;
        $tax_name = [];
        $tax_price = [];

        foreach ($cartdata as $cart) {
            $taxlist = helper::gettax($cart->tax);
            if (!empty($taxlist)) {
                foreach ($taxlist as $tax) {
                    if (!empty($tax)) {
                        $producttax = helper::taxRate($tax->tax, $cart->price, $cart->qty, $tax->type);
                        $itemTax['tax_name'] = $tax->name;
                        $itemTax['tax'] = $tax->tax;
                        $itemTax['tax_rate'] = $producttax;
                        $itemtaxes[] = $itemTax;

                        if (!in_array($tax->name, $tax_name)) {
                            $tax_name[] = $tax->name;

                            if ($tax->type == 1) {
                                $price = $tax->tax * $cart->qty;
                            }

                            if ($tax->type == 2) {
                                $price = ($tax->tax / 100) * ($cart->price);
                            }
                            $tax_price[] = $price;
                        } else {
                            if ($tax->type == 1) {
                                $price = $tax->tax * $cart->qty;
                            }

                            if ($tax->type == 2) {
                                $price = ($tax->tax / 100) * ($cart->price);
                            }
                            $tax_price[array_search($tax->name, $tax_name)] += $price;
                        }
                    }
                }
            }
        }

        $taxArr['tax'] = $tax_name;
        $taxArr['rate'] = $tax_price;

        return view('front.cart', compact('cartdata', 'vdata', 'storeinfo', 'taxArr'));
    }

    /**
     * Update cart item quantity
     * Enrichi avec gestion type minus/plus et validation complète du HomeController
     */
    public function updateQuantity(Request $request)
    {
        if ($request->cart_id == "") {
            return response()->json(["status" => 0, "message" => "Cart ID is required"], 200);
        }
        if ($request->qty == "") {
            return response()->json(["status" => 0, "message" => "Qty is required"], 200);
        }

        $cartdata = Cart::where('id', $request->cart_id)->first();
        
        if (!$cartdata) {
            return response()->json(['status' => 0, 'message' => trans('messages.cart_not_found')], 404);
        }

        $item = Item::where('id', $request->item_id)->where('vendor_id', $cartdata->vendor_id)->first();

        if (!$item) {
            return response()->json(['status' => 0, 'message' => trans('messages.item_not_found')], 404);
        }

        $variation = Variants::where('item_id', $request->item_id)->first();

        // Type "minus" : simple décrémentation
        if ($request->type == "minus") {
            Cart::where('id', $request->cart_id)->update([
                'qty' => $request->qty,
                'price' => $request->qty * $cartdata->item_price
            ]);
            return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
        }

        // Type "plus" : vérifications complètes de stock
        if ($item->has_variants == 1) {
            // Item avec variantes
            if ($variation && $variation->stock_management == 1) {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('variants_id', $variation->id)
                        ->where('id', '!=', $request->cart_id)
                        ->where('user_id', Auth::user()->id)
                        ->where('buynow', 0)
                        ->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('variants_id', $variation->id)
                        ->where('id', '!=', $request->cart_id)
                        ->where('session_id', Session::getId())
                        ->where('buynow', 0)
                        ->first();
                }

                $qty = ($cartqty->totalqty ?? 0) + $request->qty;

                if ($variation->min_order != null && $variation->min_order != "" && $variation->min_order != 0) {
                    if ($variation->min_order > $qty && $variation->min_order != $qty) {
                        return response()->json([
                            'status' => 0,
                            'message' => trans('messages.min_qty_message') . $variation->min_order,
                            'qty' => $request->qty
                        ], 200);
                    }
                }

                if ($variation->max_order != null && $variation->max_order != "" && $variation->max_order != 0) {
                    if ($variation->max_order < $qty && $variation->max_order != $qty) {
                        $message = ($cartqty->totalqty == null)
                            ? trans('messages.max_qty_message') . $variation->max_order
                            : trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $variation->max_order;
                        return response()->json([
                            'status' => 0,
                            'message' => $message,
                            'qty' => $request->qty - 1
                        ], 200);
                    }
                }

                if ($qty == $variation->qty) {
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $qty], 200);
                }

                if ($qty > $variation->qty && ($variation->qty != null && $variation->qty != "")) {
                    return response()->json([
                        'status' => 0,
                        'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name . '(' . $variation->name . ')',
                        'qty' => $request->qty - 1
                    ], 200);
                } else {
                    Cart::where('id', $request->cart_id)->update([
                        'qty' => $request->qty,
                        'price' => $request->qty * $cartdata->item_price
                    ]);
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }
            } else {
                Cart::where('id', $request->cart_id)->update([
                    'qty' => $request->qty,
                    'price' => $request->qty * $cartdata->item_price
                ]);
                return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
            }
        } elseif ($item->has_variants == 2) {
            // Item sans variantes
            if ($item->stock_management == 1) {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('item_id', $item->id)
                        ->where('id', '!=', $request->cart_id)
                        ->where('user_id', Auth::user()->id)
                        ->where('buynow', 0)
                        ->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                        ->where('item_id', $item->id)
                        ->where('id', '!=', $request->cart_id)
                        ->where('session_id', Session::getId())
                        ->where('buynow', 0)
                        ->first();
                }

                $qty = ($cartqty->totalqty ?? 0) + $request->qty;

                if ($item->min_order != null && $item->min_order != "" && $item->min_order != 0) {
                    if ($item->min_order > $qty && $item->min_order != $qty) {
                        return response()->json([
                            'status' => 0,
                            'message' => trans('messages.min_qty_message') . $item->min_order,
                            'qty' => $request->qty
                        ], 200);
                    }
                }

                if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                    if ($item->max_order < $qty) {
                        $message = ($cartqty->totalqty == null)
                            ? trans('messages.max_qty_message') . $item->max_order
                            : trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $item->max_order;
                        return response()->json([
                            'status' => 0,
                            'message' => $message,
                            'qty' => $request->qty - 1
                        ], 200);
                    }
                }

                if ($qty == $item->qty) {
                    Cart::where('id', $request->cart_id)->update([
                        'qty' => $request->qty,
                        'price' => $request->qty * $cartdata->item_price
                    ]);
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }

                if ($qty > $item->qty && ($item->qty != null && $item->qty != "")) {
                    return response()->json([
                        'status' => 0,
                        'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name,
                        'qty' => $request->qty - 1
                    ], 200);
                } else {
                    Cart::where('id', $request->cart_id)->update([
                        'qty' => $request->qty,
                        'price' => $request->qty * $cartdata->item_price
                    ]);
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }
            } else {
                Cart::where('id', $request->cart_id)->update([
                    'qty' => $request->qty,
                    'price' => $request->qty * $cartdata->item_price
                ]);
                return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
            }
        }
    }

    /**
     * Remove item from cart
     * Enrichi avec session forget pour codes promo
     */
    public function removeItem(Request $request)
    {
        if ($request->cart_id == "") {
            return response()->json(["status" => 0, "message" => "Cart Id is required"], 200);
        }

        // Récupérer le vendor_id avant suppression
        $cartItem = Cart::where('id', $request->cart_id)->first();
        $vendor_id = $cartItem ? $cartItem->vendor_id : null;

        // Supprimer l'item
        $cart = Cart::where('id', $request->cart_id)->delete();

        // Compter les items restants
        if (Auth::user() && Auth::user()->type == 3) {
            $count = Cart::where('user_id', Auth::user()->id)
                ->where('vendor_id', $vendor_id)
                ->count();
        } else {
            $count = Cart::where('session_id', Session::getId())
                ->where('vendor_id', $vendor_id)
                ->count();
        }

        // Supprimer les codes promo de la session
        session()->forget(['offer_amount', 'offer_code', 'offer_type']);

        if ($cart) {
            return response()->json(['status' => 1, 'message' => 'Success', 'cartcnt' => $count], 200);
        } else {
            return response()->json(['status' => 0], 200);
        }
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
