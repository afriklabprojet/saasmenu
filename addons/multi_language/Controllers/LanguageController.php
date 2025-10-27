<?php

namespace Addons\MultiLanguage\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

/**
 * Contrôleur pour la gestion multilingue
 */
class LanguageController extends Controller
{
    /**
     * Langues supportées
     */
    private $supportedLanguages = [
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦']
    ];

    /**
     * Changer la langue
     */
    public function changeLanguage(Request $request)
    {
        $locale = $request->input('locale', 'fr');

        if (array_key_exists($locale, $this->supportedLanguages)) {
            Session::put('locale', $locale);
            App::setLocale($locale);

            return response()->json([
                'success' => true,
                'locale' => $locale,
                'message' => 'Langue changée avec succès'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Langue non supportée'
        ], 400);
    }

    /**
     * Obtenir la langue actuelle
     */
    public function getCurrentLanguage()
    {
        $locale = Session::get('locale', config('app.locale'));

        return response()->json([
            'current_locale' => $locale,
            'language_info' => $this->supportedLanguages[$locale] ?? null
        ]);
    }

    /**
     * Obtenir toutes les langues supportées
     */
    public function getSupportedLanguages()
    {
        return response()->json([
            'supported_languages' => $this->supportedLanguages
        ]);
    }
}
