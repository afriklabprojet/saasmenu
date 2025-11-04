<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Item;
use App\Models\Banner;
use App\Models\Settings;
use App\Models\User;
use App\Models\Timing;
use App\Helpers\helper;

class RefactoredHomeController extends Controller
{
    /**
     * Display homepage
     */
    public function index(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return redirect('/select-restaurant')->with('error', 'Veuillez sélectionner un restaurant');
        }

        $settingdata = helper::appdata($vdata);

        if (!$settingdata) {
            return redirect('/select-restaurant')->with('error', 'Restaurant non trouvé');
        }

        // Check if restaurant is open
        $timing = $this->getRestaurantTiming($vdata);
        $isOpen = $this->checkIfRestaurantIsOpen($timing);

        // Get banners
        $banners = Banner::where('vendor_id', $vdata)
                        ->where('is_available', 1)
                        ->orderBy('reorder_id')
                        ->get();

        // Get categories with items
        $categories = Category::with(['items' => function($query) {
                         $query->where('is_available', 1)
                               ->orderBy('reorder_id')
                               ->limit(6);
                     }])
                     ->where('vendor_id', $vdata)
                     ->where('is_available', 1)
                     ->orderBy('reorder_id')
                     ->get();

        // Get featured items
        $featuredItems = Item::with(['variation', 'extras'])
                            ->where('vendor_id', $vdata)
                            ->where('is_available', 1)
                            ->where('is_featured', 1)
                            ->orderBy('id', 'DESC')
                            ->limit(8)
                            ->get();

        // Get top deals
        $topDeals = Item::with(['variation', 'extras'])
                       ->where('vendor_id', $vdata)
                       ->where('is_available', 1)
                       ->where('top_deals', 1)
                       ->orderBy('id', 'DESC')
                       ->limit(6)
                       ->get();

        return view('front.home', compact(
            'settingdata', 'banners', 'categories', 'featuredItems',
            'topDeals', 'vdata', 'timing', 'isOpen'
        ));
    }

    /**
     * Display categories page
     */
    public function categories(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);

        // Get categories with item count
        $categories = Category::withCount(['items' => function($query) {
                          $query->where('is_available', 1);
                      }])
                      ->where('vendor_id', $vdata)
                      ->where('is_available', 1)
                      ->orderBy('reorder_id')
                      ->get();

        return view('front.categories', compact('settingdata', 'categories', 'vdata'));
    }

    /**
     * Get restaurant timing
     */
    public function getTimeslot(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        $timing = $this->getRestaurantTiming($vdata);
        $isOpen = $this->checkIfRestaurantIsOpen($timing);

        return response()->json([
            'status' => 1,
            'is_open' => $isOpen,
            'timing' => $timing,
            'current_time' => now()->format('H:i'),
            'current_day' => now()->format('l')
        ]);
    }

    /**
     * Check subscription plan limits
     */
    public function checkPlan(Request $request)
    {
        $vdata = Session::get('restaurant_id');

        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        $planInfo = helper::getPlanInfo($vdata);

        if (!$planInfo) {
            return response()->json(['status' => 0, 'message' => 'Plan non trouvé'], 404);
        }

        // Check various limits based on current plan
        $limits = [
            'orders_limit' => $planInfo['order_limit'] ?? -1,
            'users_limit' => $planInfo['staff_limit'] ?? -1,
            'custom_domain' => $planInfo['custom_domain'] ?? 0,
            'whatsapp_message' => $planInfo['whatsapp_integration'] ?? 0,
            'google_analytics' => $planInfo['analytics'] ?? 0,
            'vendor_app' => 0, // Cette propriété n'existe pas dans getPlanInfo
            'delivery_app' => 0, // Cette propriété n'existe pas dans getPlanInfo
            'pos' => 0, // Cette propriété n'existe pas dans getPlanInfo
        ];

        return response()->json([
            'status' => 1,
            'plan_name' => $planInfo['plan_name'],
            'limits' => $limits
        ]);
    }

    /**
     * Set restaurant session
     */
    public function setRestaurant(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|integer|exists:users,id',
        ]);

        $restaurant = User::where('id', $request->restaurant_id)
                         ->where('type', 2) // Vendor type
                         ->where('is_available', 1)
                         ->first();

        if (!$restaurant) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non trouvé'], 404);
        }

        Session::put('restaurant_id', $restaurant->id);

        return response()->json([
            'status' => 1,
            'message' => 'Restaurant sélectionné',
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug
            ]
        ]);
    }

    /**
     * Get restaurant timing for the week
     */
    private function getRestaurantTiming($vendorId)
    {
        return Timing::where('vendor_id', $vendorId)
                    ->where('is_available', 1)
                    ->get()
                    ->keyBy('day');
    }

    /**
     * Check if restaurant is currently open
     */
    private function checkIfRestaurantIsOpen($timing)
    {
        $currentDay = now()->format('l'); // Full day name (Monday, Tuesday, etc.)
        $currentTime = now()->format('H:i');

        // Map day names to numbers (adjust based on your database structure)
        $dayMap = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7
        ];

        $dayNumber = $dayMap[$currentDay] ?? 1;
        $todayTiming = $timing->get($dayNumber);

        if (!$todayTiming || $todayTiming->is_closed) {
            return false;
        }

        // Check if current time is within opening hours
        $openTime = $todayTiming->open_time;
        $closeTime = $todayTiming->close_time;

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }
}
