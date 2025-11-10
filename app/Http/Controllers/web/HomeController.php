<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Item;
use App\Models\Cart;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Settings;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Timing;
use App\Models\Payment;
use App\Models\Contact;
use App\Models\Coupons;
use App\Models\Terms;
use App\Models\About;
use App\Models\Privacypolicy;
use App\Models\Banner;
use App\Models\CustomStatus;
use App\Models\SystemAddons;
use App\Helpers\helper;
use App\Models\Blog;
use App\Models\TopDeals;
use App\Models\ItemImages;
use App\Models\RefundPrivacypolicy;
use DateTime;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use DateInterval;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TableBook;
use App\Models\TableQR;
use App\Models\Variants;
use Illuminate\Support\Facades\URL;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;

class HomeController extends Controller
{
    /**
     * Méthode centralisée pour récupérer les informations du vendor et storeinfo
     * en fonction de l'host et du paramètre vendor
     */
    private function getVendorData(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];

        if ($host == env('WEBSITE_HOST') || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        } else {
            // Pour un domaine personnalisé
            $storeinfo = Settings::where('custom_domain', $host)->first();
            if (!$storeinfo) {
                // Si aucune configuration de domaine personnalisé n'est trouvée, utiliser le premier vendor
                $storeinfo = User::where('type', 1)->first();
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

    public function index(Request $request)
    {
        if ($request->tid) {
            Session::put('table_id', $request->tid);
        }

        // Utiliser la nouvelle fonction helper pour obtenir les données du vendor de manière sécurisée
        $vendorData = helper::getVendorData($request);
        $vdata = $vendorData['vendor_id'];
        $storeinfo = $vendorData['storeinfo'] ?? null;

        $getcategory = Category::where('vendor_id', $vdata)->where('is_available', '=', '1')->where('is_deleted', '2')->orderBy('reorder_id')->get();
        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            // Security fix: Use selectRaw with COALESCE instead of CASE WHEN in DB::raw
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.top_deals', '!=', '1')
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->orderBy('reorder_id', 'ASC')
                ->get();

            $topdealsproducts = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.top_deals', '1')
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->orderBy('reorder_id', 'ASC')
                ->get();
        } else {
            $getitem = Item::with(['variation', 'extras', 'item_image'])->where('items.top_deals', '!=', '1')->where('vendor_id', $vdata)->where('is_available', '1')->orderBy('reorder_id')->get();
            $topdealsproducts = Item::with(['variation', 'extras', 'item_image'])->where('items.top_deals', '1')->where('vendor_id', $vdata)->where('is_available', '1')->orderBy('reorder_id')->get();
        }
        $paymentlist = Payment::where('vendor_id', $vdata)->where('is_available', 1)->get();
        $settingdata = Settings::where('vendor_id', $vdata)->select('template')->first();

        // Vérifier si les settings existent, sinon utiliser le template par défaut
        if (!$settingdata) {
            $settingdata = (object)['template' => '1']; // Template par défaut
        }

        $bannerimage = Banner::where('vendor_id', $vdata)->orderBy('reorder_id')->get();
        $cartitems = Cart::select('id', 'item_id', 'item_name', 'item_image', 'item_price', 'extras_id', 'extras_name', 'extras_price', 'qty', 'price', 'tax', 'variants_id', 'variants_name', 'variants_price')
            ->where('vendor_id', $vdata);
        if (Auth::user() && Auth::user()->type == 3) {
            $cartitems->where('user_id', @Auth::user()->id);
        } else {
            $cartitems->where('session_id', Session::getId());
        }
        $blogs = Blog::orderBy('reorder_id')->where('vendor_id', $vdata)->get();

        $cartdata = $cartitems->get();
        if (empty($storeinfo)) {

            abort(404);
        }
        if (Auth::user() && Auth::user()->type == 3) {
            $count = Cart::where('user_id', Auth::user()->id)->where('vendor_id', $vdata)->count();
        } else {
            $count = Cart::where('session_id', Session::getId())->where('vendor_id', $vdata)->count();
        }
        session()->put('cart', $count);
        $topdeals = TopDeals::where('vendor_id', $vdata)->first();
        return view('front.template-' . $settingdata->template . '.index', compact('getcategory', 'paymentlist', 'getitem', 'vdata', 'storeinfo', 'bannerimage', 'cartdata', 'blogs', 'topdealsproducts', 'topdeals'));
    }

    public function categories(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];
        $getcategory = Category::where('vendor_id', $vdata)->where('is_available', '=', '1')->where('is_deleted', '2')->orderBy('reorder_id', 'ASC')->get();
        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
            // Security fix: Use selectRaw with COALESCE and bound parameters
            $getitem = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->orderBy('reorder_id', 'ASC')
                ->get();
        } else {
            $getitem = Item::with(['variation', 'extras', 'item_image'])->where('vendor_id', $vdata)->where('is_available', '1')->orderBy('reorder_id', 'ASC')->get();
        }
        $bannerimage = Banner::where('vendor_id', $vdata)->orderBy('reorder_id')->get();
        $cartitems = Cart::select('id', 'item_id', 'item_name', 'item_image', 'item_price', 'extras_id', 'extras_name', 'extras_price', 'qty', 'price', 'tax', 'variants_id', 'variants_name', 'variants_price')
            ->where('vendor_id', $vdata);
        if (Auth::user() && Auth::user()->type == 3) {
            $cartitems->where('user_id', @Auth::user()->id);
        } else {
            $cartitems->where('session_id', Session::getId());
        }
        $blogs = Blog::orderBy('reorder_id')->where('vendor_id', $vdata)->get();
        $cartdata = $cartitems->get();
        if (empty($storeinfo)) {

            abort(404);
        }
        if (Auth::user() && Auth::user()->type == 3) {
            $count = Cart::where('user_id', Auth::user()->id)->where('vendor_id', $vdata)->count();
        } else {
            $count = Cart::where('session_id', Session::getId())->where('vendor_id', $vdata)->count();
        }
        session()->put('cart', $count);

        return view('front.template-3.category', compact('getcategory', 'getitem', 'vdata', 'storeinfo', 'bannerimage', 'cartdata', 'blogs'));
    }
    public function user_subscribe(Request $request)
    {
        try {
            $host = $_SERVER['HTTP_HOST'];
            if ($host  ==  env('WEBSITE_HOST')) {
                $vdata = $request->id;
            }
            // if the current host doesn't contain the website domain (meaning, custom domain)
            else {
                $storeinfo = Settings::where('custom_domain', $host)->first();
                $vdata = $storeinfo->vendor_id;
            }

            $subscribe = new Subscriber;
            $subscribe->vendor_id = $vdata;
            $subscribe->email = $request->email;
            $subscribe->save();
            return redirect()->back()->with('success', trans('messages.success'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', trans('messages.wrong'));
        }
    }
    public function contact(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];

        $timings = Timing::where('vendor_id', $vdata)->get();
        return view('front.contactus', compact('vdata', 'storeinfo', 'timings', 'vdata'));
    }
    public function save_contact(Request $request)
    {
        if (
            SystemAddons::where('unique_identifier', 'google_recaptcha')->first() != null &&
            SystemAddons::where('unique_identifier', 'google_recaptcha')->first()->activated == 1
        ) {
            if (helper::appdata('')->recaptcha_version == 'v2') {
                $request->validate([
                    'g-recaptcha-response' => 'required'
                ], [
                    'g-recaptcha-response.required' => 'The g-recaptcha-response field is required.'
                ]);
            }
            if (helper::appdata('')->recaptcha_version == 'v3') {
                $score = RecaptchaV3::verify($request->get('g-recaptcha-response'), 'contact');
                if ($score <= helper::appdata('')->score_threshold) {
                    return redirect()->back()->with('error', 'You are most likely a bot');
                }
            }
        }
        $newinquiry = new Contact;
        $newinquiry->vendor_id = $request->vendor_id;
        $newinquiry->name = $request->first_name . " " . $request->last_name;
        $newinquiry->email = $request->email;
        $newinquiry->mobile = $request->mobile;
        $newinquiry->message = $request->message;
        $newinquiry->save();
        $vendordata = User::where('id', $request->vendor_id)->first();
        $emaildata = helper::emailconfigration($vendordata->id);
        Config::set('mail', $emaildata);
        helper::vendor_contact_data($vendordata->name, $vendordata->email, $request->name, $request->email, $request->mobile, $request->message);
        return redirect()->back()->with('success', trans('messages.success'));
    }


    public function table_book(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];

        return view('front.tablebook', compact('vdata','storeinfo', 'vdata'));
    }
    public function save_booking(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $vdata = $vendorData['vendor_id'];
        $table = new TableBook;
        $table->vendor_id = $vdata;
        $table->event = $request->event;
        $table->people = $request->people;
        $table->event_date = $request->event_date;
        $table->event_time = $request->event_time;
        $table->name = $request->name;
        $table->email = $request->email;
        $table->mobile = $request->mobile;
        $table->notes = $request->notes;
        $table->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }


    public function aboutus(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];

        $aboutus = About::select('about_content')->where('vendor_id', $vdata)->first();

        return view('front.about', compact('aboutus','vdata','storeinfo'));
    }
    public function terms_condition(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];
        $terms = terms::where('vendor_id', $vdata)->orderBy('id', 'ASC')->first();
        return view('front.terms_and_condition', compact('vdata','storeinfo', 'terms'));
    }
    public function privacyshow(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];

        $privacy = Privacypolicy::where('vendor_id', $vdata)->orderBy('id', 'ASC')->first();
        return view('front.privacy', compact('storeinfo','vdata', 'privacy'));
    }
    public function refundprivacypolicy(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];
        $refund_policy = RefundPrivacypolicy::where('vendor_id', $vdata)->orderBy('id', 'ASC')->first();
        return view('front.refund_policy', compact('vdata','storeinfo', 'refund_policy'));
    }
    public function addtocart(Request $request)
    {

        try {
            $host = $_SERVER['HTTP_HOST'];
            if ($host  ==  env('WEBSITE_HOST')) {
                $storeinfo = User::where('id', $request->vendor_id)->first();
                $vdata =  $storeinfo->id;
            }
            // if the current host doesn't contain the website domain (meaning, custom domain)
            else {
                $storeinfo = Settings::where('custom_domain', $host)->first();
                $vdata = $storeinfo->vendor_id;
            }

            if ($request->buynow == 1) {
                if (Auth::user() && Auth::user()->type == 3) {
                    $checkcart = Cart::where('buynow', 1)->where('user_id', Auth::user()->id)->delete();
                } else {
                    $checkcart = Cart::where('buynow', 1)->where('session_id', Session::getId())->delete();
                }
            }
            $totalprice = 0;
            $cart = new Cart;
            $variant_name = str_replace('_', ' ', $request->variants_name);
            $variation = Variants::where('name', str_replace(',', '|', $variant_name))->where('item_id', $request->item_id)->first();
            $item = Item::where('id', $request->item_id)->first();

            if (Auth::user() && Auth::user()->type == 3) {
                $cart->user_id = Auth::user()->id;
            } else {
                $cart->session_id = Session::getId();
            }

            // Security fix: Use selectRaw() instead of DB::raw() for aggregations
            if ($request->variants_name != null && $request->variants_name != "") {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $variation->id)->where('user_id', Auth::user()->id)->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $variation->id)->where('session_id', Session::getId())->first();
                }
            } else {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $request->item_id)->where('user_id', Auth::user()->id)->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $request->item_id)->where('session_id', Session::getId())->first();
                }
            }
            if ($cartqty->totalqty != null && $cartqty->totalqty != "") {
                $qty = $cartqty->totalqty + $request->qty;
            } else {
                $qty = $request->qty;
            }
            if ($request->variants_name == null && $request->variants_name == "") {

                if ($item->stock_management == 1) {
                    if ($item->min_order != null && $item->min_order != ""  && $item->min_order != 0) {
                        if ($qty < $item->min_order) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $item->min_order], 200);
                        }
                    }


                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($qty > $item->max_order) {
                            if ($cartqty->totalqty == null) {
                                return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $item->max_order], 200);
                            } else {
                                return response()->json(['status' => 0, 'message' => trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $item->max_order], 200);
                            }
                        }
                    }
                    if ($qty > $item->qty) {
                        return response()->json(['status' => 0, 'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name], 200);
                    }
                }
            } else {
                if ($variation->stock_management == 1) {
                    if ($variation->min_order != null && $variation->min_order != ""  && $variation->min_order != 0) {
                        if ($qty < $variation->min_order) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $variation->min_order], 200);
                        }
                    }


                    if ($variation->max_order != null && $variation->max_order != "" && $variation->max_order != 0) {
                        if ($qty > $variation->max_order) {
                            if ($cartqty->totalqty == null) {
                                return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $variation->max_order], 200);
                            } else {
                                return response()->json(['status' => 0, 'message' => trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $variation->max_order], 200);
                            }
                        }
                    }

                    if ($qty > $variation->qty) {
                        return response()->json(['status' => 0, 'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name . '(' . $variation->name . ')'], 200);
                    }
                }
            }
            if (!empty($variation)) {
                $cartprice = $variation->price;
                $itemprice =  $variation->price;
            } else {
                $cartprice = $request->item_price;
                $itemprice =  $request->item_price;
            }

            $extra_price = explode('|', $request->extras_price);
            if ($request->extras_price != null || $request->extras_price != "") {
                foreach ($extra_price as $price) {
                    $cartprice  = $cartprice +  $price;
                }
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
            $cart->buynow = $request->buynow;
            $cart->save();
            if (Auth::user() && Auth::user()->type == 3) {
                $count = Cart::where('user_id', Auth::user()->id)->where('vendor_id', $vdata)->where('buynow', 0)->count();
            } else {
                $count = Cart::where('session_id', Session::getId())->where('vendor_id', $vdata)->where('buynow', 0)->count();
            }
            if (Auth::user() && Auth::user()->type == 3) {
                $totalcart = helper::getcartcount($request->vendor_id, Auth::user() && Auth::user()->type == 3 ? Auth::user()->id : "");
            } else {
                $totalcart = helper::getcartcount($request->vendor_id, '');
            }
            session()->put('cart', $count);
            session()->put('vendor_id', $request->vendor_id);
            session()->put('old_session_id', Session::getId());
            $checkouturl = URL::to($storeinfo->slug . '/checkout?buy_now=' . $request->buynow);

            return response()->json(['status' => 1, 'message' => $request->item_name . ' ' . 'has been added to your cart', 'totalcart' => $totalcart, 'buynow' => $request->buynow, 'checkouturl' => $checkouturl], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['status' => 0, 'message' => $e], 400);
        }
    }
    public function details(Request $request)
    {

        $host = $_SERVER['HTTP_HOST'];

        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = User::where('id', $request->vendor_id)->where('is_available', 1)->where('is_deleted', 2)->first();
            $vdata = $request->vendor_id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }

        // Security fix: Use selectRaw with bound parameters instead of DB::raw with concatenation
        $assetsUrl = url(config('app.assets_path_url', env('ASSETSPATHURL')) . 'item/');
        
        $getitem = Item::with(['variation', 'extras','item_image'])->select(
            'id',
            'item_original_price',
            'image',
            'description',
            'tax',
            'has_variants',
            'has_extras',
            'variants_json',
            'min_order',
            'max_order',
            'qty',
            'low_qty',
            'stock_management',
            'item_name',
            'item_price',
            'item_original_price',
            'vendor_id',
            'is_available',
            'top_deals'

        )->where('id', $request->id)->where('vendor_id', $request->vendor_id)->first();
        
        // Add image_url as attribute after query
        if ($getitem && $getitem->image) {
            $getitem->image_url = $assetsUrl . '/' . $getitem->image;
        }
        $getitem->variants_json = json_decode($getitem->variants_json, true);

        // Security fix: Add image_url via accessor instead of DB::raw
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
        // return response()->json(['ResponseCode' => 1, 'ResponseText' => 'Success', 'variants' => $variants, 'extras' => $extras, 'getitem' => $getitem,'itemimages' => $itemimages], 200);
    }
    public function cart(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];

        $cartitems = Cart::select('id', 'item_id', 'item_name', 'item_image', 'item_price', 'extras_id', 'extras_name', 'extras_price', 'qty', 'price', 'tax', 'variants_id', 'variants_name', 'variants_price')
            ->where('vendor_id', $vdata);
        if (Auth::user() && Auth::user()->type == 3) {
            $cartitems->where('user_id', @Auth::user()->id);
        } else {
            $cartitems->where('session_id', Session::getId());
        }
        $cartdata = $cartitems->where('buynow',0)->get();

        //  product count tax
        $itemtaxes = [];
        $producttax = 0;
        $tax_name = [];
        $tax_price = [];

        foreach ($cartdata as $cart) {
            $taxlist =  helper::gettax($cart->tax);
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
        return view('front.cart', compact('cartdata', 'vdata','storeinfo', 'taxArr'));
    }
    public function qtyupdate(Request $request)
    {

        if ($request->cart_id == "") {
            return response()->json(["status" => 0, "message" => "Cart ID is required"], 200);
        }
        if ($request->qty == "") {
            return response()->json(["status" => 0, "message" => "Qty is required"], 200);
        }
        $cartdata = Cart::where('id', $request->cart_id)->first();
        $item = Item::where('id', $request->item_id)->where('vendor_id', $cartdata->vendor_id)->first();

        $variation = Variants::where('item_id', $request->item_id)->first();
        if ($request->type == "minus") {
            $update = Cart::where('id', $request->cart_id)->update(['qty' => $request->qty, 'price' => $request->qty * $cartdata->item_price]);
            return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
        } else {
            if ($item->has_variants == 1) {

                if ($variation->stock_management == 1) {
                    if (Auth::user() && Auth::user()->type == 3) {
                        $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $variation->id)->where('id', '!=', $request->cart_id)->where('user_id', Auth::user()->id)->where('buynow', 0)->first();
                    } else {

                        $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $variation->id)->where('id', '!=', $request->cart_id)->where('session_id', Session::getId())->where('buynow', 0)->first();
                    }

                    if ($cartqty->totalqty != null && $cartqty->totalqty != "") {
                        $qty = $cartqty->totalqty + $request->qty;
                    } else {
                        $qty = $request->qty;
                    }
                    if ($variation->min_order != null && $variation->min_order != "" && $variation->min_order != 0) {
                        if ($variation->min_order > $qty && $variation->min_order != $qty) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $variation->min_order, 'qty' => $request->qty], 200);
                        }
                    }
                    if ($variation->max_order != null && $variation->max_order != "" && $variation->max_order != 0) {
                        if ($variation->max_order < $qty && $variation->max_order != $qty) {
                            if ($cartqty->totalqty == null) {
                                return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $variation->max_order, 'qty' => $request->qty - 1], 200);
                            } else {
                                return response()->json(['status' => 0, 'message' => trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $variation->max_order, 'qty' => $request->qty - 1], 200);
                            }
                        }
                    }
                    if ($qty == $variation->qty) {
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $qty], 200);
                    }
                    if ($qty > $variation->qty  && ($variation->qty != null && $variation->qty != "")) {
                        return response()->json(['status' => 0, 'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name . '(' . $variation->name . ')', 'qty' => $request->qty - 1], 200);
                    } else {
                        $update = Cart::where('id', $request->cart_id)->update(['qty' => $request->qty, 'price' => $request->qty * $cartdata->item_price]);
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                    }
                } else {
                    $update = Cart::where('id', $request->cart_id)->update(['qty' => $request->qty, 'price' => $request->qty * $cartdata->item_price]);
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }
            } elseif ($item->has_variants == 2) {

                if ($item->stock_management == 1) {
                    if (Auth::user() && Auth::user()->type == 3) {
                        $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $item->id)->where('id', '!=', $request->cart_id)->where('user_id', Auth::user()->id)->where('buynow', 0)->first();
                    } else {

                        $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $item->id)->where('id', '!=', $request->cart_id)->where('session_id', Session::getId())->where('buynow', 0)->first();
                    }

                    if ($cartqty->totalqty != null && $cartqty->totalqty != "") {
                        $qty = $cartqty->totalqty + $request->qty;
                    } else {
                        $qty = $request->qty;
                    }

                    if ($item->min_order != null && $item->min_order != "" && $item->min_order != 0) {
                        if ($item->min_order > $qty && $item->min_order != $qty) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $item->min_order, 'qty' => $request->qty], 200);
                        }
                    }

                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($item->max_order < $qty) {
                            if ($cartqty->totalqty == null) {
                                return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $item->max_order, 'qty' => $request->qty - 1], 200);
                            } else {
                                return response()->json(['status' => 0, 'message' => trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $item->max_order, 'qty' => $request->qty - 1], 200);
                            }
                        }
                    }
                    if ($qty == $item->qty) {

                        $update = Cart::where('id', $request->cart_id)->update(['qty' => $request->qty, 'price' => $request->qty * $cartdata->item_price]);
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                    }
                    if ($qty > $item->qty  && ($item->qty != null && $item->qty != "")) {

                        return response()->json(['status' => 0, 'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name, 'qty' => $request->qty - 1], 200);
                    } else {

                        $update = Cart::where('id', $request->cart_id)->update(['qty' => $request->qty, 'price' => $request->qty * $cartdata->item_price]);

                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                    }
                } else {
                    $update = Cart::where('id', $request->cart_id)->update(['qty' => $request->qty, 'price' => $request->qty * $cartdata->item_price]);

                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }
            }
        }
    }
    public function deletecartitem(Request $request)
    {
        if ($request->cart_id == "") {
            return response()->json(["status" => 0, "message" => "Cart Id is required"], 200);
        }

        // Get cart item before deleting to get vendor_id
        $cartItem = Cart::where('id', $request->cart_id)->first();
        $vendor_id = $cartItem ? $cartItem->vendor_id : null;

        $cart = Cart::where('id', $request->cart_id)->delete();

        if (Auth::user() && Auth::user()->type == 3) {
            $count = Cart::where('user_id', Auth::user()->id)->where('vendor_id', $vendor_id)->count();
        } else {
            $count = Cart::where('session_id', Session::getId())->where('vendor_id', $vendor_id)->count();
        }
        session()->forget(['offer_amount', 'offer_code', 'offer_type']);
        if ($cart) {
            return response()->json(['status' => 1, 'message' => 'Success', 'cartcnt' => $count], 200);
        } else {
            return response()->json(['status' => 0], 200);
        }
    }
    public function checkout(Request $request)
    {
        $vendorData = $this->getVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vendor_id'];


        if ($request->buy_now == 1) {
            $cartitems = Cart::select('id', 'item_id', 'item_name', 'item_image', 'item_price', 'extras_id', 'extras_name', 'extras_price', 'qty', 'price', 'tax', 'variants_id', 'variants_name', 'variants_price')
                ->where('vendor_id', $vdata)->where('buynow', 1);
        } else {
            $cartitems = Cart::select('id', 'item_id', 'item_name', 'item_image', 'item_price', 'extras_id', 'extras_name', 'extras_price', 'qty', 'price', 'tax', 'variants_id', 'variants_name', 'variants_price')
                ->where('vendor_id', $vdata)->where('buynow', 0);
        }
        if (Auth::user() && Auth::user()->type == 3) {
            $cartitems->where('user_id', @Auth::user()->id);
        } else {
            $cartitems->where('session_id', Session::getId());
        }
        $cartdata = $cartitems->get();

        if (count($cartdata) == 0) {
            return redirect($storeinfo->slug . '/cart')->with('error', trans('messages.cart_empty'));
        }
        foreach ($cartdata as $cart) {
            if ($cart->variants_id != "" && $cart->variants_id != null) {
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $cart->variants_id)->where('user_id', Auth::user()->id)->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $cart->variants_id)->where('session_id', Session::getId())->first();
                }
                $variant = Variants::where('id', $cart->variants_id)->first();
                $item_name = Item::select('item_name')->where('id', $cart->item_id)->first();
                if ($variant->stock_management == 1) {

                    if ($variant->min_order != null && $variant->min_order != ""  && $variant->min_order != 0) {
                        if ($cartqty->totalqty < $variant->min_order) {
                            return redirect()->back()->with('error', trans('messages.min_qty_message') . $variant->min_order . " " . ($item_name->item_name));
                        }
                    }
                    if ($variant->max_order != null && $variant->max_order != "" && $variant->max_order != 0) {
                        if ($variant->max_order < $cartqty->totalqty) {
                            return redirect()->back()->with('error', trans('messages.max_qty_message') . $variant->max_order . ' ' . ($item_name->item_name));
                        }
                    }
                    if ($cart->qty > $variant->qty) {
                        return redirect()->back()->with('error', trans('messages.cart_qty_msg') . ' ' . trans('labels.out_of_stock_msg') . ' ' . $item_name->item_name . '(' . $variant->name . ')');
                    }
                }
            } else {

                $item = Item::where('id', $cart->item_id)->first();

                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $cart->item_id)->where('user_id', Auth::user()->id)->first();
                } else {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $cart->item_id)->where('session_id', Session::getId())->first();
                }

                if ($item->stock_management == 1) {
                    if ($item->min_order != null && $item->min_order != ""  && $item->min_order != 0) {
                        if ($cartqty->totalqty < $item->min_order) {
                            return redirect()->back()->with('error', trans('messages.min_qty_message') . $item->min_order . ' ' . ($item->item_name));
                        }
                    }

                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($item->max_order < $cartqty->totalqty) {
                            return redirect()->back()->with('error', trans('messages.max_qty_message') . $item->max_order . ' ' . ($item->item_name));
                        }
                    }
                    if ($cart->qty > $item->qty) {
                        return redirect()->back()->with('error',  trans('messages.cart_qty_msg') . ' ' . trans('labels.out_of_stock_msg') . ' ' . $item->item_name);
                    }
                }
            }
        }

        //  product count tax
        $itemtaxes = [];
        $producttax = 0;
        $tax_name = [];
        $tax_price = [];

        foreach ($cartdata as $cart) {
            $taxlist =  helper::gettax($cart->tax);
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
        $deliveryarea = DeliveryArea::where('vendor_id', $vdata)->orderBy('reorder_id')->get();
        $paymentlist = Payment::where('is_available', '1')->where('vendor_id', $vdata)->where('is_activate', 1)->orderBy('reorder_id')->get();

        $dt = date('Y-m-d');
        $coupons = Coupons::where('active_from', '<=', $dt)
            ->where('active_to', '>=', $dt)
            ->where('vendor_id', $vdata)
            ->orderBy('reorder_id', 'DESC')
            ->get();
        $tableqrs = TableQR::where('vendor_id', $vdata)->orderBy('id', 'ASC')->get();

        return view('front.checkout', compact('cartdata', 'deliveryarea', 'vdata','storeinfo', 'paymentlist', 'coupons', 'tableqrs', 'vdata', 'taxArr'));
    }

    public function applypromocode(Request $request)
    {

        if ($request->promocode == "") {
            return response()->json(["status" => 0, "message" => trans('messages.enter_promocode')], 200);
        }
        $promocode = Coupons::where('code', $request->promocode)->where('vendor_id', $request->vendor_id)->first();
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
            } else {
                return response()->json(['status' => 0, 'message' => trans('messages.limit_over')], 200);
            }
        } else {
            return response()->json(['status' => 0, 'message' => trans('messages.promocode_expired')], 200);
        }


        if (@$promocode->code == $request->promocode) {
            return response()->json(['status' => 1, 'message' => trans('messages.promocode_applied'), 'data' => $promocode], 200);
        } else {
            return response()->json(['status' => 0, 'message' => trans('messages.wrong_promocode')], 200);
        }
    }
    public function removepromocode(Request $request)
    {
        $remove = session()->forget(['offer_amount', 'offer_code', 'offer_type']);
        if (!$remove) {
            return response()->json(['status' => 1, 'message' => trans('messages.promocode_removed')], 200);
        } else {
            return response()->json(['status' => 0, 'message' => trans('messages.wrong')], 200);
        }
    }
    public function timeslot(Request $request)
    {
        try {
            $host = $_SERVER['HTTP_HOST'];
            if ($host  ==  env('WEBSITE_HOST')) {
                $storeinfo = helper::storeinfo($request->vendor);
                $vdata = $storeinfo->id;
            }
            // if the current host doesn't contain the website domain (meaning, custom domain)
            else {
                $storeinfo = Settings::where('custom_domain', $host)->first();
                $vdata = $storeinfo->vendor_id;
            }

            $timezone = helper::appdata($vdata);

            $slots = [];
            date_default_timezone_set($timezone->timezone);

            if ($request->inputDate != "" || $request->inputDate != null) {
                $day = date('l', strtotime($request->inputDate));

                $minute = "";
                $time = Timing::where('vendor_id', $vdata)->where('day', $day)->first();
                if ($time->is_always_close == 1) {
                    $slots = "1";
                } else {
                    if (helper::appdata($vdata)->interval_type == 2) {
                        $minute = (float)helper::appdata($vdata)->interval_time * 60;
                    }
                    if (helper::appdata($vdata)->interval_type == 1) {
                        $minute = helper::appdata($vdata)->interval_time;
                    }
                    $duration = $minute;
                    $cleanup = 0;
                    $start = $time->open_time;
                    $break_start = $time->break_start; // break start
                    $break_end   = $time->break_end; // break end
                    $end = $time->close_time;
                    $firsthalf = self::firsthalf($duration, $cleanup, $start, $break_start);
                    $secondhalf = self::secondhalf($duration, $cleanup, $break_end, $end);
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
                            'slot' =>  $item,
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
    function firsthalf($duration, $cleanup, $start, $break_start)
    {
        $start = new DateTime($start);
        $break_start  = new DateTime($break_start);
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
    function secondhalf($duration, $cleanup, $break_end, $end)
    {
        $break_end = new DateTime($break_end);
        $end  = new DateTime($end);
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
    public function checkplan(Request $request)
    {
        $checkplan = helper::checkplan($request->vendor_id, '3');
        return $checkplan;
    }
    public function paymentmethod(Request $request)
    {
        if ($request->payment_type == 6) {
            $vendor_id = $request->modal_vendor_id;
        } else {
            $vendor_id = $request->vendor_id;
        }
        if ($request->payment_type == 6) {
            $buynow = $request->modal_buynow;
        } else {
            $buynow = $request->buynow;
        }
        $payment_id = "";
        $user_id = "";
        $session_id = "";
        $filename = "";
        $storeinfo = helper::storeinfo($request->vendor);
        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
        } else {
            $session_id = session()->getId();
        }
        // Security fix: Use leftJoin with whereRaw in closure instead of DB::raw in join condition
        $cartitems = Cart::select('carts.id', 'carts.item_id', 'carts.item_name', 'carts.item_image', 'carts.item_price', 'carts.extras_name', 'carts.extras_price', 'carts.qty', 'carts.price', 'carts.tax', 'carts.variants_id', 'carts.variants_name', 'carts.variants_price')
            ->selectRaw('GROUP_CONCAT(tax.name) as name')
            ->leftJoin('tax', function($join) {
                $join->whereRaw('FIND_IN_SET(tax.id, carts.tax) > 0');
            })
            ->where('carts.vendor_id', $vendor_id);
        if (Auth::user() && Auth::user()->type == 3) {
            $cartitems->where('carts.user_id', @$user_id);
        } else {
            $cartitems->where('carts.session_id', $session_id);
        }
        if ($buynow == 1) {
            $cartitems = $cartitems->where('carts.buynow', 1);
        } else {
            $cartitems = $cartitems->where('carts.buynow', 0);
        }
        $cartdata = $cartitems->groupBy("carts.id")->get();

        if ($cartdata->count() == 0) {
            return response()->json(['status' => 0, 'message' => trans('messages.cart_empty')], 200);
        }

        foreach ($cartdata as $cart) {
            if ($cart->variants_id != "" && $cart->variants_id != null) {
                $variant = Variants::where('id', $cart->variants_id)->first();
                $item_name = Item::select('item_name')->where('id', $cart->item_id)->first();
                if ($variant->stock_management == 1) {
                    if ($variant->min_order != null && $variant->min_order != ""  && $variant->min_order != 0) {
                        if ($cart->qty < $variant->min_order) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $variant->min_order . ' ' . ($item_name->item_name)], 200);
                        }
                    }


                    if ($variant->max_order != null && $variant->max_order != "" && $variant->max_order != 0) {
                        if ($cart->qty > $variant->max_order) {
                            return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $variant->max_order . ' ' . ($item_name->item_name)], 200);
                        }
                    }
                    if ($cart->qty > $variant->qty) {
                        return response()->json(['status' => 0, 'message' => trans($variant->name . 'qty not enough for order !!')], 200);
                    }
                }
            } else {
                $item = Item::where('id', $cart->item_id)->first();
                if ($item->stock_management == 1) {
                    if ($item->min_order != null && $item->min_order != ""  && $item->min_order != 0) {
                        if ($cart->qty < $item->min_order) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $item->min_order . ' ' . ($item->item_name)], 200);
                        }
                    }


                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($cart->qty > $item->max_order) {
                            return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $item->max_order . ' ' . ($item->item_name)], 200);
                        }
                    }
                    if ($cart->qty > $item->qty) {
                        return response()->json(['status' => 0, 'message' => trans($item->name . 'qty not enough for order !!')], 200);
                    }
                }
            }
        }

        $payment_id = "";
        $user_id = "";
        $session_id = "";
        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
        } else {
            $session_id = session()->getId();
            $user_id = null;
        }
        $payment_id = $request->payment_id;
        if ($request->payment_type == "3") {
            $getstripe = Payment::select('environment', 'secret_key', 'currency')->where('payment_type', '3')->where('vendor_id', $request->vendor_id)->first();
            $skey = $getstripe->secret_key;
            Stripe::setApiKey($skey);
            $customer = Customer::create(
                array(
                    'email' => $request->customer_email,
                    'source' =>  $request->stripeToken,
                    'name' => $request->customer_name,
                )
            );
            $charge = Charge::create(
                array(
                    'customer' => $customer->id,
                    'amount' => $request->grand_total * 100,
                    'currency' => $getstripe->currency,
                    'description' => 'Restro',
                )
            );
            if ($request->payment_id == "") {
                $payment_id = $charge['id'];
            } else {
                $payment_id = $request->payment_id;
            }
        }
        if ($request->payment_type == '16') {
            // CinetPay payment processing
            $payment_id = "";
            $orderresponse = helper::createorder($request->vendor_id, $user_id, $session_id, $request->payment_type, $payment_id, $request->customer_email, $request->customer_name, $request->customer_mobile, $request->stripeToken, $request->grand_total, $request->delivery_charge, $request->address, $request->building, $request->landmark, $request->postal_code, $request->discount_amount, $request->offer_type, $request->sub_total, $request->tax, $request->tax_name, $request->delivery_time, $request->delivery_date, $request->delivery_area, $request->couponcode, $request->order_type, $request->notes, $request->table, $filename, $buynow);

            if ($orderresponse == -1) {
                $url = URL::to(@$request->slug . "/cart");
                return response()->json(['status' => 0, 'message' => trans('messages.cart_empty'), "url" =>  $url]);
            }

            if ($orderresponse == "false") {
                return response()->json(['status' => 0, 'message' => trans('order not placed without default status !!')], 200);
            }

            // Initialize CinetPay payment
            $cinetpay_data = [
                'vendor_id' => $request->vendor_id,
                'order_number' => $orderresponse,
                'amount' => $request->grand_total,
                'customer_email' => $request->customer_email,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_mobile ?? ''
            ];

            // Call CinetPay controller
            $cinetpay_controller = new \App\Http\Controllers\addons\CinetPayController();
            $cinetpay_request = new \Illuminate\Http\Request($cinetpay_data);
            $cinetpay_response = $cinetpay_controller->initPayment($cinetpay_request);
            $response_data = json_decode($cinetpay_response->getContent(), true);

            if ($response_data['status'] == 1) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Redirecting to payment...',
                    'payment_url' => $response_data['payment_url'],
                    'order_number' => $orderresponse
                ], 200);
            } else {
                return response()->json(['status' => 0, 'message' => $response_data['message']], 400);
            }
        } elseif ($request->payment_type == '6') {
            if ($request->hasFile('screenshot')) {
                $validator = Validator::make($request->all(), [
                    'screenshot' => 'image|mimes:jpg,jpeg,png',
                ], [
                    'screenshot.mage' => trans('messages.enter_image_file'),
                    'screenshot.mimes' => trans('messages.valid_image'),
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                } else {
                    $filename = 'screenshot-' . uniqid() . "." . $request->file('screenshot')->getClientOriginalExtension();
                    $request->file('screenshot')->move(env('ASSETPATHURL') . 'admin-assets/images/screenshot/', $filename);
                }
            }
            $payment_id = "";
            $orderresponse = helper::createorder($request->modal_vendor_id, $user_id, $session_id, $request->payment_type, $payment_id, $request->modal_customer_email, $request->modal_customer_name, $request->modal_customer_mobile, $request->stripeToken, $request->modal_grand_total, $request->modal_delivery_charge, $request->modal_address, $request->modal_building, $request->modal_landmark, $request->modal_postal_code, $request->modal_discount_amount, $request->modal_offer_type, $request->modal_subtotal, $request->modal_tax, $request->modal_tax_name, $request->modal_delivery_time, $request->modal_delivery_date, $request->modal_delivery_area, $request->modal_couponcode, $request->modal_order_type, $request->modal_notes, $request->modal_table, $filename, $buynow);
            $vendor = User::where('id', $request->modal_vendor_id)->first();
            if ($orderresponse == -1) {
                $url = URL::to(@$request->slug . "/cart");
                return response()->json(['status' => 0, 'message' => trans('messages.cart_empty'), "url" =>  $url]);
            }
            if ($request->offer_type == "promocode") {
                if ($request->couponcode != null) {
                    $promocode = Coupons::where('code', $request->couponcode)->where('vendor_id', $request->vendor_id)->first();
                    $promocode->limit = $promocode->limit - 1;
                    $promocode->save();
                }
            }

            if ($orderresponse == "false") {
                return redirect()->back()->with('error', trans('order not placed without default status !!'));
            } else {
                return redirect($request->slug . '/success/' . $orderresponse)->with('success', trans('messages.order_placed'));
            }
        } else {
            $orderresponse = helper::createorder($request->vendor_id, $user_id, $session_id, $request->payment_type, $payment_id, $request->customer_email, $request->customer_name, $request->customer_mobile, $request->stripeToken, $request->grand_total, $request->delivery_charge, $request->address, $request->building, $request->landmark, $request->postal_code, $request->discount_amount, $request->offer_type, $request->sub_total, $request->tax, $request->tax_name, $request->delivery_time, $request->delivery_date, $request->delivery_area, $request->couponcode, $request->order_type, $request->notes, $request->table, $filename, $buynow);
            $vendor = User::where('id', $request->vendor_id)->first();
            if ($orderresponse == -1) {
                $url = URL::to(@$request->slug . "/cart");
                return response()->json(['status' => 0, 'message' => trans('messages.cart_empty'), "url" =>  $url]);
            }
            if ($request->offer_type == "promocode") {
                if ($request->couponcode != null) {
                    $promocode = Coupons::where('code', $request->couponcode)->where('vendor_id', $request->vendor_id)->first();
                    $promocode->limit = $promocode->limit - 1;
                    $promocode->save();
                }
            }

            $url = URL::to(@$request->slug . "/success/" . $orderresponse);
            if ($orderresponse == "false") {
                return response()->json(['status' => 0, 'message' => trans('order not placed without default status !!')], 200);
            } else {
                return response()->json(['status' => 1, 'message' => trans('messages.order_placed'), "order_number" => $orderresponse, "url" =>  $url], 200);
            }
        }
    }
    public function ordersuccess(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }
        $order_number = $request->order_number;
        $whmessage = helper::whatsappmessage($request->order_number, $vdata, $storeinfo);
        return view('front.ordersuccess', compact('vdata','storeinfo', 'order_number', 'whmessage'));
    }
    public function trackorder(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }

        // Security fix: Use selectRaw instead of DB::raw for DATE_FORMAT
        $status = Order::select('order_number', 'address', 'building', 'landmark', 'pincode', 'order_type', 'id', 'discount_amount', 'status','status_type','order_notes', 'tax', 'tax_name', 'delivery_charge', 'couponcode', 'offer_type', 'sub_total', 'grand_total', 'customer_name', 'customer_email', 'mobile')
            ->selectRaw('DATE_FORMAT(created_at, "%d %M %Y") as date')
            ->where('order_number', $request->ordernumber)
            ->where('vendor_id',$vdata)
            ->first();
        $orderdata = Order::with('tableqr')->where('order_number', $request->ordernumber)->where('vendor_id',$vdata)->first();
        $orderdetails = OrderDetails::where('order_details.order_id', $status->id)->get();
        $summery = array(
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
            'status_type'=> $status->status_type,
            'order_type' => $status->order_type,
            'couponcode' => $status->couponcode,
            'offer_type' => $status->offer_type,
            'sub_total' => $status->sub_total,
            'grand_total' => $status->grand_total,
            'customer_name' => $status->customer_name,
            'customer_email' => $status->customer_email,
            'mobile' => $status->mobile,

        );

        return view('front.track-order', compact('vdata','storeinfo', 'orderdata', 'summery', 'orderdetails'));
    }
    public function cancelorder(Request $request, $order_number)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }
        $orderdata = Order::where('order_number', $request->ordernumber)->where('vendor_id', $vdata)->first();

        $orderdetail = OrderDetails::where('order_id', $orderdata->id)->get();

        if ($orderdata->status_type == 2) {
            return redirect()->back()->with('error', trans('messages.already_accepted'));
        } else if ($orderdata->status_type == 4) {
            return redirect()->back()->with('error', trans('messages.already_rejected'));
        } else if ($orderdata->status_type == 3) {
            return redirect()->back()->with('error', trans('messages.already_delivered'));
        }
        $defaultsatus = CustomStatus::where('vendor_id', $storeinfo->id)->where('order_type', $orderdata->order_type)->where('type', 4)->where('is_available', 1)->where('is_deleted', 2)->first();
        if (empty($defaultsatus) && $defaultsatus == null) {
            return redirect()->back()->with('error', trans('messages.wrong'));
        } else {
            $orderdata->status_type = $defaultsatus->type;
            $orderdata->status = $defaultsatus->id;
            $orderdata->update();
            foreach ($orderdetail as $order) {
                if ($order->variants_id != null && $order->variants_id != "") {
                    $item = Variants::where('id', $order->variants_id)->where('item_id', $order->item_id)->first();
                } else {
                    $item = Item::where('id', $order->item_id)->where('vendor_id', $storeinfo->id)->first();
                }
                $item->qty = $item->qty + $order->qty;
                $item->update();
            }
            $title = helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type, $storeinfo->id)->name;

            $message_text = 'Order ' . $orderdata->order_number . ' has been cancelled by' . $orderdata->user_name;
            $emaildata = helper::emailconfigration($storeinfo->id);
            Config::set('mail', $emaildata);
            $checkmail = helper::cancel_order($storeinfo->email, $storeinfo->name, $title, $message_text, $orderdata);
            $emaildata = User::select('id', 'name', 'slug', 'email', 'mobile', 'token')->where('id', $orderdata->vendor_id)->first();
            $body = "#" . $request->order_number . " has been cancelled";
            helper::push_notification($emaildata->token, $title, $body, "order", $orderdata->id);
            return redirect()->back()->with('success', trans('messages.success'));
        }
    }
    public function ordercreate(Request $request)
    {

        if ($request->paymentId != "") {
            $paymentid = $request->paymentId;
        }
        if ($request->payment_id != "") {
            $paymentid = $request->payment_id;
        }
        if ($request->transaction_id != "") {
            $paymentid = $request->transaction_id;
        }

        if (session()->get('payment_type') == "11") {
            if ($request->code == "PAYMENT_SUCCESS") {
                $paymentid = $request->transactionId;
            }
        }

        if (Session::get('payment_type') == "12") {
            $checkstatus = app('App\Http\Controllers\addons\PayTabController')->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));

            if ($checkstatus == "A") {
                $paymentid = Session::get('tran_ref');
            } else {
                return redirect(Session::get('failureurl'))->with('error', session()->get('paytab_response'));
            }
        }

        if (Session::get('payment_type') == "13") {
            $checkstatus = app('App\Http\Controllers\addons\MollieController')->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));

            if ($checkstatus == "A") {
                $paymentid = Session::get('tran_ref');
            } else {
                return redirect(Session::get('failureurl'))->with('error', session()->get('paytab_response'));
            }
        }

        if (Session::get('payment_type') == "14") {
            if ($request->status == "Completed") {
                $paymentid = $request->transaction_id;
            } else {
                return redirect(Session::get('failureurl'))->with('error', session()->get('paytab_response'));
            }
        }

        if (session()->get('payment_type') == "15") {

            $checkstatus = app('App\Http\Controllers\addons\XenditController')->checkpaymentstatus(session()->get('tran_ref'), Session::get('vendor_id'));

            if ($checkstatus == "PAID") {
                $paymentid = session()->get('payment_id');
            } else {
                return redirect(Session::get('failureurl'))->with('error', session()->get('paytab_response'));
            }
        }

        $user_id = "";
        $session_id = "";
        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;
        } else {
            $session_id = session()->getId();
        }
        $orderresponse = helper::createorder(Session::get('vendor_id'), $user_id, $session_id, Session::get('payment_type'), $paymentid, Session::get('customer_email'), Session::get('customer_name'), Session::get('customer_mobile'), Session::get('stripeToken'), Session::get('grand_total'), Session::get('delivery_charge'), Session::get('address'), Session::get('building'), Session::get('landmark'), Session::get('postal_code'), Session::get('discount_amount'), Session::get('offer_type'), Session::get('sub_total'), Session::get('tax'), Session::get('tax_name'), Session::get('delivery_time'), Session::get('delivery_date'), Session::get('delivery_area'), Session::get('couponcode'), Session::get('order_type'), Session::get('notes'), Session::get('table'),'',Session::get('buynow'));

        $slug = Session::get('slug');
        $order_number = $orderresponse;

        return view('front.mercadoorder', compact('slug', 'order_number'));
    }
    public function search(Request $request)
    {

        $user_id = @Auth::user()->id;
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }
        $getsearchitems = array();

        if ($user_id != null) {

            if ($request->has('search') && $request->search != "") {
                // Security fix: Use selectRaw with COALESCE and bound parameters
                $getsearchitems = Item::with(['variation', 'extras'])
                    ->select('items.*')
                    ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                    ->where('items.vendor_id', $vdata)
                    ->where('is_available', '1')
                    ->where('items.top_deals', '!=', 1)
                    ->where('items.item_name', 'LIKE', '%' . $request->search . '%')
                    ->orderBy('id', 'ASC')
                    ->get();
            }
        } else {

            if ($request->has('search') && $request->search != "") {
                $getsearchitems = Item::with(['variation', 'extras'])->where('vendor_id', $vdata)->where('is_available', '1')->where('top_deals', '!=', 1)->where('item_name', 'LIKE', '%' . $request->search . '%')->orderBy('id', 'ASC')->get();
            }
        }


        return view('front.search', compact('getsearchitems', 'vdata','storeinfo'));
    }
    public function changeqty(Request $request)
    {
        try {

            if ($request->variants_name == null) {

                $item = Item::where('id', $request->item_id)->where('vendor_id', $request->vendor_id)->first();

                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $item->id)->where('user_id', Auth::user()->id)->where('buynow', 0)->first();
                } else {

                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('item_id', $item->id)->where('session_id', Session::getId())->where('buynow', 0)->first();
                }
                if ($cartqty->totalqty != null && $cartqty->totalqty != "") {
                    $qty = $cartqty->totalqty + $request->qty;
                } else {
                    $qty = $request->qty;
                }

                if ($item->stock_management == 1) {
                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($item->max_order < $qty) {
                            if ($cartqty->totalqty == null) {
                                return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $item->max_order, 'qty' => $request->qty - 1], 200);
                            } else {
                                return response()->json(['status' => 0, 'message' => trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $item->max_order, 'qty' => $request->qty - 1], 200);
                            }
                        }
                    }
                    if ($qty == $item->qty) {
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $qty], 200);
                    }
                    if ($qty > $item->qty && ($item->qty != null && $item->qty != "")) {
                        return response()->json(['status' => 0, 'message' => trans('labels.out_of_stock_msg') . ' ' . $item->item_name, 'qty' => $request->qty - 1], 200);
                    } else {
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                    }
                } else {
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }
            } else {
                $variant_name = str_replace('_', ' ', $request->variants_name);
                $item = Variants::where('name', str_replace(',', '|', $variant_name))->where('item_id', $request->item_id)->first();
                $item_name = Item::select('item_name')->where('id', $request->item_id)->first();
                if (Auth::user() && Auth::user()->type == 3) {
                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $item->id)->where('user_id', Auth::user()->id->where('buynow', 0))->first();
                } else {

                    $cartqty = Cart::selectRaw('SUM(qty) as totalqty')->where('variants_id', $item->id)->where('session_id', Session::getId())->where('buynow', 0)->first();
                }

                if ($cartqty->totalqty != null && $cartqty->totalqty != "") {
                    $qty = $cartqty->totalqty + $request->qty;
                } else {
                    $qty = $request->qty;
                }

                if ($item->stock_management == 1) {
                    if ($item->min_order != null && $item->min_order != "" && $item->min_order != 0) {
                        if ($item->min_order > $qty && $item->min_order != $qty) {
                            return response()->json(['status' => 0, 'message' => trans('messages.min_qty_message') . $item->min_order, 'qty' => $request->qty], 200);
                        }
                    }
                    if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                        if ($item->max_order < $qty && $item->max_order != $qty) {
                            if ($cartqty->totalqty == null) {
                                return response()->json(['status' => 0, 'message' => trans('messages.max_qty_message') . $item->max_order, 'qty' => $request->qty - 1], 200);
                            } else {
                                return response()->json(['status' => 0, 'message' => trans('messages.cart_qty_msg') . ' ' . trans('messages.max_qty_message') . $item->max_order, 'qty' => $request->qty - 1], 200);
                            }
                        }
                    }
                    if ($qty == $item->qty) {
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $qty], 200);
                    }
                    if ($qty > $item->qty  && ($item->qty != null && $item->qty != "")) {
                        return response()->json(['status' => 0, 'message' => trans('labels.out_of_stock_msg') . ' ' . $item_name->item_name . '(' . $item->name . ')', 'qty' => $request->qty - 1], 200);
                    } else {
                        return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                    }
                } else {
                    return response()->json(['status' => 1, 'message' => 'success', 'qty' => $request->qty], 200);
                }
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', trans('messages.wrong'));
        }
    }
    public function getProductsVariantQuantity(Request $request)
    {

        $quantity = $variant_id = 0;
        $price = 0;
        $item = Item::where('id', $request->item_id)->first();
        $variant = Variants::where('item_id', $request->item_id)->where('name', implode('|', str_replace('_', ' ', $request->name)))->first();
        $status = 1;
        $quantity = @$variant->qty - (isset($cart[@$variant->id]['qty']) ? $cart[@$variant->id]['qty'] : 0);
        $price = @$variant->price;
        $original_price = @$variant->original_price;
        $variant_id = @$variant->id;
        $min_order = @$variant->min_order;
        $max_order = @$variant->max_order;
        $stock_management = @$variant->stock_management;
        $variants_name = @$request->name;
        if ($item->is_available == 2 || $item->is_deleted == 1) {
            $is_available = 2;
        } else {
            $is_available = @$variant->is_available;
        }
        return response()->json([
            'status' => 1, 'price' => $price, 'original_price' => $original_price, 'quantity' => $quantity,
            'variant_id' => $variant_id,
            'min_order' => $min_order,
            'max_order' => $max_order,
            'stock_management' => $stock_management,
            'variants_name' => $variants_name,
            'is_available' => $is_available
        ], 200);
    }

    public function alltopdeals(Request $request)
    {

        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfo = Settings::where('custom_domain', $host)->first();
            $vdata = $storeinfo->vendor_id;
        }

        if (empty($storeinfo)) {
            abort(404);
        }
        if (Auth::user() && Auth::user()->type == 3) {
            $user_id = Auth::user()->id;

            // Security fix: Use selectRaw with COALESCE and bound parameters
            $topdealsproducts = Item::with(['variation', 'extras', 'item_image'])
                ->select('items.*')
                ->selectRaw('COALESCE((SELECT 1 FROM favorite WHERE favorite.item_id = items.id AND favorite.user_id = ? LIMIT 1), 0) as is_favorite', [$user_id])
                ->where('items.top_deals', '1')
                ->where('items.vendor_id', $vdata)
                ->where('is_available', '1')
                ->orderBy('reorder_id', 'ASC')
                ->get();
        } else {

            $topdealsproducts = Item::with(['variation', 'extras', 'item_image'])->where('items.top_deals', '1')->where('vendor_id', $vdata)->where('is_available', '1')->orderBy('reorder_id')->get();
        }
        $topdeals = TopDeals::where('vendor_id', $storeinfo->id)->first();
        return view('front.viewalltopdeals', compact('topdealsproducts', 'vdata','storeinfo', 'topdeals'));
    }
}
