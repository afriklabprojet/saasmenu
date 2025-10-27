<?php

return [
    'name' => 'Multi-Language Support',
    'description' => 'Support multilingue avec changement de langue dynamique',
    'version' => '1.0.0',
    'author' => 'RestroSaaS Team',
    'routes' => [
        'web' => __DIR__ . '/routes/web.php',
    ],
    'middleware' => [
        'App\Http\Middleware\LocalizationMiddleware'
    ],
    'views' => [
        'namespace' => 'multi-language',
        'path' => __DIR__ . '/views'
    ],
    'languages' => [
        'fr' => [
            'name' => 'Français',
            'flag' => '🇫🇷',
            'direction' => 'ltr'
        ],
        'en' => [
            'name' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr'
        ],
        'ar' => [
            'name' => 'العربية',
            'flag' => '🇸🇦',
            'direction' => 'rtl'
        ]
    ],
    'requires' => [
        'laravel' => '>=8.0'
    ]
];
