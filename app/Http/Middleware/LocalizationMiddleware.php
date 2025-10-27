<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

/**
 * LocalizationMiddleware
 * Middleware principal pour l'addon multi_language
 * Gère la détection et application automatique de la langue
 */
class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     * Implémentation de l'addon multi_language
     */
    public function handle(Request $request, Closure $next)
    {
        // multi_language: Langues supportées (FR/EN/AR)
        $supportedLocales = ['fr', 'en', 'ar'];

        // Détection de la langue
        $locale = $this->detectLocale($request, $supportedLocales);

        // Application de la langue
        App::setLocale($locale);
        Session::put('locale', $locale);

        // Configuration des formats de dates en français
        if ($locale === 'fr') {
            Config::set('app.locale', 'fr');
            setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');
        }

        return $next($request);
    }

    /**
     * Détecte la langue appropriée
     */
    private function detectLocale(Request $request, array $supportedLocales): string
    {
        // 1. Paramètre URL (?lang=fr)
        if ($request->has('lang') && in_array($request->get('lang'), $supportedLocales)) {
            return $request->get('lang');
        }

        // 2. Session utilisateur
        if (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales)) {
            return Session::get('locale');
        }

        // 3. Préférence utilisateur connecté
        if (auth()->check() && auth()->user()->preferred_locale) {
            $userLocale = auth()->user()->preferred_locale;
            if (in_array($userLocale, $supportedLocales)) {
                return $userLocale;
            }
        }

        // 4. En-tête Accept-Language du navigateur
        $browserLocale = $this->getBrowserLocale($request, $supportedLocales);
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Langue par défaut (français)
        return 'fr';
    }

    /**
     * Détecte la langue préférée du navigateur
     */
    private function getBrowserLocale(Request $request, array $supportedLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse de l'en-tête Accept-Language
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';q=', trim($lang));
            $locale = trim($parts[0]);
            $quality = isset($parts[1]) ? (float) $parts[1] : 1.0;

            // Extraction du code langue (ex: fr-FR -> fr)
            $langCode = substr($locale, 0, 2);

            if (in_array($langCode, $supportedLocales)) {
                $languages[$langCode] = $quality;
            }
        }

        if (empty($languages)) {
            return null;
        }

        // Tri par qualité (préférence)
        arsort($languages);

        // Retourne la langue avec la plus haute qualité
        return array_key_first($languages);
    }
}
