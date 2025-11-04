<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Item;
use App\Models\ItemImages;
use App\Models\TopDeals;
use App\Models\Category;
use App\Models\Cart;
use App\Helpers\helper;

class ProductController extends Controller
{
    /**
     * Display product details
     */
    public function details(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $product = Item::with(['variation', 'extras'])
                      ->where('id', $request->id)
                      ->where('vendor_id', $vdata)
                      ->where('is_available', 1)
                      ->first();

        if (!$product) {
            return redirect('/')->with('error', 'Produit non trouvé');
        }

        $settingdata = helper::appdata($vdata);
        
        // Get product images
        $itemImages = ItemImages::select([
                'id', 'image', 'item_id',
                DB::raw("CONCAT('" . url(env('ASSETSPATHURL') . 'item/') . "/', image) AS image_url")
            ])
            ->where('item_id', $request->id)
            ->orderBy('reorder_id')
            ->get();

        // Get related products (same category)
        $relatedProducts = Item::with(['variation'])
                              ->where('cat_id', $product->cat_id)
                              ->where('vendor_id', $vdata)
                              ->where('is_available', 1)
                              ->where('id', '!=', $product->id)
                              ->limit(6)
                              ->get();

        return view('front.product-details', compact(
            'settingdata', 'product', 'itemImages', 'relatedProducts', 'vdata'
        ));
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:100',
        ]);

        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        $searchTerm = strip_tags($request->search);
        
        // Search in items
        $items = Item::with(['variation', 'extras'])
                    ->where('vendor_id', $vdata)
                    ->where('is_available', 1)
                    ->where('top_deals', '!=', 1)
                    ->where(function($query) use ($searchTerm) {
                        $query->where('item_name', 'LIKE', "%{$searchTerm}%")
                              ->orWhere('item_description', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orderBy('item_name', 'ASC')
                    ->paginate(12);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'status' => 1,
                'items' => $items->items(),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                    'per_page' => $items->perPage()
                ]
            ]);
        }

        // Regular page request
        $settingdata = helper::appdata($vdata);
        
        return view('front.search-results', compact(
            'settingdata', 'items', 'searchTerm', 'vdata'
        ));
    }

    /**
     * Get top deals products
     */
    public function topDeals(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        
        // Get top deals items
        $topDealsItems = Item::with(['variation', 'extras'])
                            ->where('vendor_id', $vdata)
                            ->where('is_available', 1)
                            ->where('top_deals', 1)
                            ->orderBy('id', 'DESC')
                            ->paginate(12);

        // Add cart quantity for each item
        foreach ($topDealsItems as $item) {
            $item->cart_qty = $this->getItemCartQuantity($item->id);
            
            // Add variation cart quantities
            if ($item->variation) {
                foreach ($item->variation as $variant) {
                    $variant->cart_qty = $this->getVariantCartQuantity($variant->id);
                }
            }
        }

        return view('front.top-deals', compact('settingdata', 'topDealsItems', 'vdata'));
    }

    /**
     * Get products by category
     */
    public function getByCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        $category = Category::where('id', $request->category_id)
                           ->where('vendor_id', $vdata)
                           ->where('is_available', 1)
                           ->first();

        if (!$category) {
            return response()->json(['status' => 0, 'message' => 'Catégorie non trouvée'], 404);
        }

        $items = Item::with(['variation', 'extras'])
                    ->where('cat_id', $request->category_id)
                    ->where('vendor_id', $vdata)
                    ->where('is_available', 1)
                    ->orderBy('reorder_id')
                    ->paginate(12);

        // Add cart quantities
        foreach ($items as $item) {
            $item->cart_qty = $this->getItemCartQuantity($item->id);
            
            if ($item->variation) {
                foreach ($item->variation as $variant) {
                    $variant->cart_qty = $this->getVariantCartQuantity($variant->id);
                }
            }
        }

        return response()->json([
            'status' => 1,
            'category' => $category,
            'items' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
                'per_page' => $items->perPage()
            ]
        ]);
    }

    /**
     * Get product variations and prices
     */
    public function getVariations(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
        ]);

        $vdata = Session::get('restaurant_id');
        
        $item = Item::with(['variation', 'extras'])
                   ->where('id', $request->item_id)
                   ->where('vendor_id', $vdata)
                   ->where('is_available', 1)
                   ->first();

        if (!$item) {
            return response()->json(['status' => 0, 'message' => 'Produit non trouvé'], 404);
        }

        // Add cart quantities to variations
        if ($item->variation) {
            foreach ($item->variation as $variant) {
                $variant->cart_qty = $this->getVariantCartQuantity($variant->id);
            }
        }

        return response()->json([
            'status' => 1,
            'item' => $item,
            'variations' => $item->variation,
            'extras' => $item->extras
        ]);
    }

    /**
     * Get featured products
     */
    public function getFeatured(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'items' => []]);
        }

        $featuredItems = Item::with(['variation', 'extras'])
                            ->where('vendor_id', $vdata)
                            ->where('is_available', 1)
                            ->where('is_featured', 1)
                            ->orderBy('id', 'DESC')
                            ->limit(8)
                            ->get();

        // Add cart quantities
        foreach ($featuredItems as $item) {
            $item->cart_qty = $this->getItemCartQuantity($item->id);
        }

        return response()->json([
            'status' => 1,
            'items' => $featuredItems
        ]);
    }

    /**
     * Check product availability
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'variation_id' => 'nullable|integer|exists:variants,id',
            'qty' => 'required|integer|min:1',
        ]);

        $vdata = Session::get('restaurant_id');
        
        $item = Item::where('id', $request->item_id)
                   ->where('vendor_id', $vdata)
                   ->where('is_available', 1)
                   ->first();

        if (!$item) {
            return response()->json(['status' => 0, 'available' => false, 'message' => 'Produit non disponible']);
        }

        $available = true;
        $message = '';
        $maxQty = 0;

        if ($item->stock_management == 1) {
            if ($request->variation_id) {
                $variant = $item->variation()->where('id', $request->variation_id)->first();
                if ($variant) {
                    $maxQty = $variant->stock;
                    $currentCartQty = $this->getVariantCartQuantity($request->variation_id);
                } else {
                    return response()->json(['status' => 0, 'available' => false, 'message' => 'Variation non trouvée']);
                }
            } else {
                $maxQty = $item->qty;
                $currentCartQty = $this->getItemCartQuantity($request->item_id);
            }

            $requestedTotal = $currentCartQty + $request->qty;
            
            if ($requestedTotal > $maxQty) {
                $available = false;
                $message = "Stock insuffisant. Disponible: {$maxQty}, En panier: {$currentCartQty}";
            }
        }

        return response()->json([
            'status' => 1,
            'available' => $available,
            'message' => $message,
            'max_qty' => $maxQty,
            'current_cart_qty' => $currentCartQty ?? 0
        ]);
    }

    /**
     * Get item cart quantity
     */
    private function getItemCartQuantity($itemId)
    {
        $query = Cart::where('item_id', $itemId)->where('buynow', 0);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', Session::getId());
        }

        return $query->sum('qty') ?: 0;
    }

    /**
     * Get variant cart quantity
     */
    private function getVariantCartQuantity($variantId)
    {
        $query = Cart::where('variants_id', $variantId)->where('buynow', 0);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', Session::getId());
        }

        return $query->sum('qty') ?: 0;
    }
}