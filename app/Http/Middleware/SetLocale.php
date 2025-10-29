<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = ['en', 'fr', 'ar'];
        $locale = 'en'; // Default locale
        
        // Priority order for locale detection:
        // 1. URL parameter (temporary)
        // 2. Session stored locale
        // 3. Cookie stored locale  
        // 4. Accept-Language header
        // 5. Default (en)
        
        if ($request->has('lang') && in_array($request->get('lang'), $supportedLocales)) {
            $locale = $request->get('lang');
        } elseif (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales)) {
            $locale = Session::get('locale');
        } elseif ($request->hasCookie('locale') && in_array($request->cookie('locale'), $supportedLocales)) {
            $locale = $request->cookie('locale');
        } elseif ($request->server('HTTP_ACCEPT_LANGUAGE')) {
            $preferredLang = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (in_array($preferredLang, $supportedLocales)) {
                $locale = $preferredLang;
            }
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
}