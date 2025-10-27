<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Language Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'addon multi_language
    | Support multilingue FR/EN/AR avec changement dynamique
    |
    */

    // Addon multi_language - Langues supportées
    'supported_locales' => [
        'fr' => [
            'name' => 'Français',
            'native' => 'Français',
            'flag' => '🇫🇷',
            'direction' => 'ltr',
            'iso_code' => 'fr_FR'
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr',
            'iso_code' => 'en_US'
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'direction' => 'rtl',
            'iso_code' => 'ar_SA'
        ]
    ],

    // Langue par défaut pour multi_language
    'default_locale' => env('APP_LOCALE', 'fr'),

    // Stockage de la langue (session/cookie)
    'storage' => 'session',

    // Fallback langue si non trouvée
    'fallback_locale' => 'fr',

    // Middleware automatique
    'middleware_enabled' => true,

    // Détection automatique de la langue du navigateur
    'auto_detect' => true,

    // Routes à exclure de la localisation
    'exclude_routes' => [
        'api/*',
        'webhooks/*'
    ]
];
