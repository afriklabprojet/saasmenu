<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch application language
     *
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, $locale)
    {
        // Validate locale
        $supportedLocales = ['en', 'fr', 'ar'];

        if (! in_array($locale, $supportedLocales)) {
            $locale = 'en'; // Default to English
        }

        // Set application locale
        App::setLocale($locale);

        // Store locale in session
        Session::put('locale', $locale);

        // Store locale in cookie for persistence
        $cookie = cookie('locale', $locale, 60 * 24 * 30); // 30 days

        return redirect()->back()->withCookie($cookie);
    }

    /**
     * Get current locale
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function current()
    {
        return response()->json([
            'current_locale' => app()->getLocale(),
            'available_locales' => [
                'en' => 'English',
                'fr' => 'Français',
                'ar' => 'العربية',
            ],
        ]);
    }
}
