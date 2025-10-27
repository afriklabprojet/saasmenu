<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

/**
 * MultiLanguageController
 * Contrôleur d'administration pour l'addon multi_language
 */
class MultiLanguageController extends Controller
{
    /**
     * Liste des langues supportées par multi_language
     */
    private $supportedLanguages = [
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'direction' => 'ltr'],
        'en' => ['name' => 'English', 'flag' => '🇺🇸', 'direction' => 'ltr'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦', 'direction' => 'rtl']
    ];

    /**
     * Page d'administration de l'addon multi_language
     */
    public function index()
    {
        $data = [
            'title' => 'Multi-Language Administration',
            'current_locale' => App::getLocale(),
            'supported_languages' => $this->supportedLanguages,
            'session_locale' => Session::get('locale', config('app.locale')),
            'stats' => $this->getLanguageStats()
        ];

        return view('admin.multi-language.index', $data);
    }

    /**
     * Changer la langue - endpoint pour multi_language
     */
    public function changeLanguage(Request $request)
    {
        $locale = $request->input('locale', 'fr');

        if (array_key_exists($locale, $this->supportedLanguages)) {
            Session::put('locale', $locale);
            App::setLocale($locale);

            // Configuration spécifique selon la langue
            $this->configureLanguageSettings($locale);

            return response()->json([
                'success' => true,
                'locale' => $locale,
                'language_info' => $this->supportedLanguages[$locale],
                'message' => 'Language changed successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unsupported language'
        ], 400);
    }

    /**
     * Configuration des paramètres selon la langue
     */
    private function configureLanguageSettings($locale)
    {
        // Configuration multi_language
        switch ($locale) {
            case 'ar':
                Config::set('app.locale', 'ar');
                Config::set('app.timezone', 'Asia/Riyadh');
                break;
            case 'en':
                Config::set('app.locale', 'en');
                Config::set('app.timezone', 'America/New_York');
                break;
            default:
                Config::set('app.locale', 'fr');
                Config::set('app.timezone', 'Europe/Paris');
                break;
        }
    }

    /**
     * Statistiques d'utilisation des langues
     */
    private function getLanguageStats()
    {
        return [
            'total_languages' => count($this->supportedLanguages),
            'current_language' => App::getLocale(),
            'default_language' => config('app.locale'),
            'rtl_support' => true,
            'addon_version' => '1.0.0'
        ];
    }

    /**
     * Test des traductions pour multi_language
     */
    public function testTranslations()
    {
        $translations = [];

        foreach ($this->supportedLanguages as $locale => $info) {
            App::setLocale($locale);
            $translations[$locale] = [
                'welcome' => __('Welcome'),
                'home' => __('Home'),
                'menu' => __('Menu'),
                'language_name' => $info['name']
            ];
        }

        return response()->json([
            'success' => true,
            'translations' => $translations,
            'supported_languages' => $this->supportedLanguages
        ]);
    }
}
