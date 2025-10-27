<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Loyalty Program Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the restaurant loyalty program system
    |
    */

    // Système de points
    'points_per_euro' => env('LOYALTY_POINTS_PER_EURO', 1),
    'euro_per_point' => env('LOYALTY_EURO_PER_POINT', 0.01),
    
    // Points bonus
    'welcome_points' => env('LOYALTY_WELCOME_POINTS', 100),
    'birthday_points' => env('LOYALTY_BIRTHDAY_POINTS', 50),
    'referral_points' => env('LOYALTY_REFERRAL_POINTS', 200),
    'review_points' => env('LOYALTY_REVIEW_POINTS', 25),
    'social_share_points' => env('LOYALTY_SOCIAL_SHARE_POINTS', 10),
    
    // Règles métier
    'min_order_for_points' => env('LOYALTY_MIN_ORDER_FOR_POINTS', 10.00),
    'points_expiry_days' => env('LOYALTY_POINTS_EXPIRY_DAYS', 365),
    'max_points_per_order' => env('LOYALTY_MAX_POINTS_PER_ORDER', 1000),
    
    // Paramètres généraux
    'is_active' => env('LOYALTY_IS_ACTIVE', true),
    'auto_tier_upgrade' => env('LOYALTY_AUTO_TIER_UPGRADE', true),
    'email_notifications' => env('LOYALTY_EMAIL_NOTIFICATIONS', true),
    'sms_notifications' => env('LOYALTY_SMS_NOTIFICATIONS', false),
    
    // Niveaux par défaut
    'default_tiers' => [
        [
            'name' => 'Bronze',
            'min_points' => 0,
            'max_points' => 500,
            'multiplier' => 1.0,
            'color' => '#CD7F32',
            'benefits' => ['Points sur chaque achat']
        ],
        [
            'name' => 'Argent',
            'min_points' => 501,
            'max_points' => 1500,
            'multiplier' => 1.2,
            'color' => '#C0C0C0',
            'benefits' => ['20% de points bonus', 'Offres exclusives']
        ],
        [
            'name' => 'Or',
            'min_points' => 1501,
            'max_points' => 5000,
            'multiplier' => 1.5,
            'color' => '#FFD700',
            'benefits' => ['50% de points bonus', 'Livraison gratuite', 'Support prioritaire']
        ],
        [
            'name' => 'Platine',
            'min_points' => 5001,
            'max_points' => null,
            'multiplier' => 2.0,
            'color' => '#E5E4E2',
            'benefits' => ['Points doublés', 'Accès VIP', 'Cadeaux exclusifs']
        ]
    ],
    
    // Types de récompenses
    'reward_types' => [
        'discount_percentage' => 'Remise en pourcentage',
        'discount_fixed' => 'Remise fixe',
        'free_item' => 'Article gratuit',
        'free_delivery' => 'Livraison gratuite',
        'cashback' => 'Cashback',
        'gift' => 'Cadeau'
    ],
    
    // Intégrations
    'integrations' => [
        'email_provider' => env('LOYALTY_EMAIL_PROVIDER', 'mailgun'),
        'sms_provider' => env('LOYALTY_SMS_PROVIDER', 'twilio'),
        'analytics_provider' => env('LOYALTY_ANALYTICS_PROVIDER', 'google'),
    ]
];