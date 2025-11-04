<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\About;
use App\Models\Terms;
use App\Models\Privacypolicy;
use App\Models\RefundPrivacypolicy;
use App\Helpers\helper;

class PageController extends Controller
{
    /**
     * Display about us page
     */
    public function aboutUs(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        $aboutus = About::where('vendor_id', $vdata)->first();

        if (!$aboutus) {
            return redirect('/')->with('error', 'Page À propos non disponible');
        }

        return view('front.about-us', compact('settingdata', 'aboutus', 'vdata'));
    }

    /**
     * Display terms and conditions
     */
    public function termsConditions(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        $terms = Terms::where('vendor_id', $vdata)->first();

        if (!$terms) {
            return redirect('/')->with('error', 'Conditions d\'utilisation non disponibles');
        }

        return view('front.terms-conditions', compact('settingdata', 'terms', 'vdata'));
    }

    /**
     * Display privacy policy
     */
    public function privacyPolicy(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        $privacypolicy = Privacypolicy::where('vendor_id', $vdata)->first();

        if (!$privacypolicy) {
            return redirect('/')->with('error', 'Politique de confidentialité non disponible');
        }

        return view('front.privacy-policy', compact('settingdata', 'privacypolicy', 'vdata'));
    }

    /**
     * Display refund privacy policy
     */
    public function refundPrivacyPolicy(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return redirect('/')->with('error', 'Restaurant non sélectionné');
        }

        $settingdata = helper::appdata($vdata);
        $refundprivacypolicy = RefundPrivacypolicy::where('vendor_id', $vdata)->first();

        if (!$refundprivacypolicy) {
            return redirect('/')->with('error', 'Politique de remboursement non disponible');
        }

        return view('front.refund-privacy-policy', compact('settingdata', 'refundprivacypolicy', 'vdata'));
    }

    /**
     * Get page content via API (for AJAX requests)
     */
    public function getPageContent(Request $request)
    {
        $request->validate([
            'page_type' => 'required|in:about,terms,privacy,refund',
        ]);

        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'message' => 'Restaurant non sélectionné'], 400);
        }

        $content = null;

        switch ($request->page_type) {
            case 'about':
                $content = About::where('vendor_id', $vdata)
                               ->select(['about_content', 'updated_at'])
                               ->first();
                break;
                
            case 'terms':
                $content = Terms::where('vendor_id', $vdata)
                               ->select(['terms_content', 'updated_at'])
                               ->first();
                break;
                
            case 'privacy':
                $content = Privacypolicy::where('vendor_id', $vdata)
                                      ->select(['privacy_content', 'updated_at'])
                                      ->first();
                break;
                
            case 'refund':
                $content = RefundPrivacypolicy::where('vendor_id', $vdata)
                                            ->select(['refund_content', 'updated_at'])
                                            ->first();
                break;
        }

        if (!$content) {
            return response()->json(['status' => 0, 'message' => 'Contenu non disponible'], 404);
        }

        return response()->json([
            'status' => 1,
            'content' => $content,
            'last_updated' => $content->updated_at ? $content->updated_at->format('d/m/Y H:i') : null
        ]);
    }

    /**
     * Check if page exists for vendor
     */
    public function checkPageAvailability(Request $request)
    {
        $request->validate([
            'page_type' => 'required|in:about,terms,privacy,refund',
        ]);

        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'available' => false]);
        }

        $exists = false;

        switch ($request->page_type) {
            case 'about':
                $exists = About::where('vendor_id', $vdata)->exists();
                break;
                
            case 'terms':
                $exists = Terms::where('vendor_id', $vdata)->exists();
                break;
                
            case 'privacy':
                $exists = Privacypolicy::where('vendor_id', $vdata)->exists();
                break;
                
            case 'refund':
                $exists = RefundPrivacypolicy::where('vendor_id', $vdata)->exists();
                break;
        }

        return response()->json([
            'status' => 1,
            'available' => $exists
        ]);
    }

    /**
     * Get all available pages for vendor
     */
    public function getAvailablePages(Request $request)
    {
        $vdata = Session::get('restaurant_id');
        
        if (empty($vdata)) {
            return response()->json(['status' => 0, 'pages' => []]);
        }

        $pages = [
            'about' => About::where('vendor_id', $vdata)->exists(),
            'terms' => Terms::where('vendor_id', $vdata)->exists(),
            'privacy' => Privacypolicy::where('vendor_id', $vdata)->exists(),
            'refund' => RefundPrivacypolicy::where('vendor_id', $vdata)->exists(),
        ];

        return response()->json([
            'status' => 1,
            'pages' => $pages
        ]);
    }
}