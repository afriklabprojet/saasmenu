<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Models\Settings;
use App\Models\Banner;
use App\Models\TopDeals;
use App\Models\ItemImages;
use App\Helpers\helper;
use Illuminate\Support\Facades\App;

/**
 * MenuController
 * 
 * Gère l'affichage du catalogue produits et menu :
 * - Page d'accueil avec catégories et produits
 * - Liste des catégories
 * - Détails des produits
 * - Recherche de produits
 * - Top deals
 * - Quantités disponibles des variantes
 * 
 * Refactorisé depuis HomeController pour améliorer la maintenabilité
 */
class MenuController extends Controller
{
    /**
     * Récupère les données du vendor en fonction du host
     */
    private function getVendorData(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];

        if ($host == env('WEBSITE_HOST') || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        } else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            if (!$storeinfo) {
                $storeinfo = User::where('type', 2)->first();
                $vdata = $storeinfo->id;
            } else {
                $vdata = $storeinfo->vendor_id;
            }
        }

        return [
            'storeinfo' => $storeinfo,
            'vendor_id' => $vdata
        ];
    }

    /**
     * Page d'accueil avec catégories et produits
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->tid) {
            Session::put('table_id', $request->tid);
        }

        $vendorData = helper::getVendorData($request);
        $vdata = $vendorData['vendor_id'];
        $storeinfo = $vendorData['storeinfo'] ?? null;

        $getcategory = Category::where('vendor_id', $vdata)
            ->where('is_available', '=', '1')
            ->where('is_deleted', '2')
            ->orderBy('reorder_id')
            ->get();

        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.top_deals', '!=', '1')
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        } else {
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->where('top_deals', '!=', '1')
                ->where('vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        }

        $banners = Banner::where('vendor_id', $vdata)->get();
        $topdeals = TopDeals::where('vendor_id', $vdata)->first();

        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            $gettopdeals = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.top_deals', '1')
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        } else {
            $gettopdeals = Item::with(['variation', 'extras', 'item_image'])
                ->where('top_deals', '1')
                ->where('vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        }

        return view('front.home', compact('getcategory', 'getitem', 'vdata', 'banners', 'topdeals', 'gettopdeals'));
    }

    /**
     * Affiche les produits d'une catégorie spécifique
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $vdata = $vendorData['vendor_id'];

        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.cat_id', $request->category_id)
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        } else {
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->where('cat_id', $request->category_id)
                ->where('vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        }

        $html = view('front.category', compact('getitem', 'vdata'))->render();
        return response()->json(['status' => 1, 'output' => $html], 200);
    }

    /**
     * Affiche les détails d'un produit
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];

        if ($host == env('WEBSITE_HOST')) {
            $storeinfo = User::where('id', $request->vendor_id)
                ->where('is_available', 1)
                ->where('is_deleted', 2)
                ->first();
            $vdata = $request->vendor_id;
        } else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }

        $assetsUrl = url(config('app.assets_path_url', env('ASSETSPATHURL')) . 'item/');

        $getitem = Item::with(['variation', 'extras', 'item_image'])
            ->select(
                'id', 'item_original_price', 'image', 'description', 'tax',
                'has_variants', 'has_extras', 'variants_json', 'min_order',
                'max_order', 'qty', 'low_qty', 'stock_management', 'item_name',
                'item_price', 'item_original_price', 'vendor_id', 'is_available', 'top_deals'
            )
            ->where('id', $request->id)
            ->where('vendor_id', $request->vendor_id)
            ->first();

        if ($getitem && $getitem->image) {
            $getitem->image_url = $assetsUrl . '/' . $getitem->image;
        }

        $getitem->variants_json = json_decode($getitem->variants_json, true);

        $itemimages = ItemImages::select('id', 'image', 'item_id')
            ->where('item_id', $request->id)
            ->orderBy('reorder_id')
            ->get()
            ->map(function($image) use ($assetsUrl) {
                $image->image_url = $assetsUrl . '/' . $image->image;
                return $image;
            });

        App::setLocale(session()->get('locale'));

        $topdeals = TopDeals::where('vendor_id', $request->vendor_id)->first();
        $html = view('front.product.productdetail', compact('getitem', 'itemimages', 'vdata', 'topdeals'))->render();
        
        return response()->json(['status' => 1, 'output' => $html], 200);
    }

    /**
     * Recherche de produits
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $vdata = $vendorData['vendor_id'];

        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.vendor_id', $vdata)
                ->where('items.item_name', 'like', '%' . $request->name . '%')
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        } else {
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->where('vendor_id', $vdata)
                ->where('item_name', 'like', '%' . $request->name . '%')
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        }

        $html = view('front.searchitem', compact('getitem', 'vdata'))->render();
        return response()->json(['status' => 1, 'output' => $html], 200);
    }

    /**
     * Affiche tous les top deals
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function alltopdeals(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $vdata = $vendorData['vendor_id'];

        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            $gettopdeals = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.top_deals', '1')
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        } else {
            $gettopdeals = Item::with(['variation', 'extras', 'item_image'])
                ->where('top_deals', '1')
                ->where('vendor_id', $vdata)
                ->where('is_available', '1')
                ->where('is_deleted', '2')
                ->orderBy('reorder_id')
                ->get();
        }

        $html = view('front.topdeals', compact('gettopdeals', 'vdata'))->render();
        return response()->json(['status' => 1, 'output' => $html], 200);
    }

    /**
     * Récupère les quantités disponibles pour les variantes d'un produit
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsVariantQuantity(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $vdata = $vendorData['vendor_id'];

        $item = Item::with(['variation'])
            ->where('id', $request->id)
            ->where('vendor_id', $vdata)
            ->first();

        if (!$item) {
            return response()->json(['status' => 0, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'status' => 1,
            'item' => $item,
            'variations' => $item->variation
        ], 200);
    }
}
