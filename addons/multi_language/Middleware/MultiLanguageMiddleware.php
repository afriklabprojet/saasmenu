<?php

namespace Addons\MultiLanguage\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Middleware de localisation pour l'addon multi-language
 * Note: Le middleware principal est App\Http\Middleware\LocalizationMiddleware
 */
class MultiLanguageMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtenir la langue depuis la session ou utiliser la langue par défaut
        $locale = Session::get('locale', config('app.locale', 'fr'));

        // Valider la langue
        $supportedLanguages = ['fr', 'en', 'ar'];
        if (!in_array($locale, $supportedLanguages)) {
            $locale = 'fr';
        }

        // Appliquer la langue
        App::setLocale($locale);

        // Définir la direction du texte dans le header
        $direction = ($locale === 'ar') ? 'rtl' : 'ltr';
        view()->share('language_direction', $direction);
        view()->share('current_locale', $locale);

        return $next($request);
    }
}
