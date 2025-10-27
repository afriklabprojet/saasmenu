<?php

return [
    /*
    |--------------------------------------------------------------------------
    | POS System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Point of Sale system
    |
    */

    // Paramètres de taxation
    'tax_rate' => env('POS_TAX_RATE', 20.0), // En pourcentage
    'service_charge' => env('POS_SERVICE_CHARGE', 0.0), // Montant fixe
    'tax_included' => env('POS_TAX_INCLUDED', true),

    // Configuration des reçus
    'receipt_header' => env('POS_RECEIPT_HEADER', ''),
    'receipt_footer' => env('POS_RECEIPT_FOOTER', 'Merci de votre visite !'),
    'receipt_logo' => env('POS_RECEIPT_LOGO', ''),
    'auto_print_receipt' => env('POS_AUTO_PRINT_RECEIPT', true),
    'receipt_copies' => env('POS_RECEIPT_COPIES', 1),

    // Paramètres de remise
    'allow_discount' => env('POS_ALLOW_DISCOUNT', true),
    'max_discount_percentage' => env('POS_MAX_DISCOUNT_PERCENTAGE', 50),
    'max_discount_amount' => env('POS_MAX_DISCOUNT_AMOUNT', 100),
    'require_manager_approval' => env('POS_REQUIRE_MANAGER_APPROVAL', false),

    // Informations client
    'require_customer_info' => env('POS_REQUIRE_CUSTOMER_INFO', false),
    'loyalty_integration' => env('POS_LOYALTY_INTEGRATION', true),
    'customer_display' => env('POS_CUSTOMER_DISPLAY', false),

    // Moyens de paiement
    'default_payment_method' => env('POS_DEFAULT_PAYMENT_METHOD', 'cash'),
    'accepted_payment_methods' => [
        'cash' => 'Espèces',
        'card' => 'Carte bancaire',
        'contactless' => 'Sans contact',
        'mobile' => 'Paiement mobile',
        'voucher' => 'Bon d\'achat',
        'loyalty_points' => 'Points fidélité'
    ],

    // Configuration de la devise
    'currency_symbol' => env('POS_CURRENCY_SYMBOL', '€'),
    'currency_code' => env('POS_CURRENCY_CODE', 'EUR'),
    'currency_position' => env('POS_CURRENCY_POSITION', 'after'), // 'before' ou 'after'
    'decimal_places' => env('POS_DECIMAL_PLACES', 2),

    // Paramètres de sécurité
    'session_timeout' => env('POS_SESSION_TIMEOUT', 480), // En minutes
    'require_pin' => env('POS_REQUIRE_PIN', false),
    'pin_length' => env('POS_PIN_LENGTH', 4),
    'lock_after_attempts' => env('POS_LOCK_AFTER_ATTEMPTS', 3),

    // Configuration du terminal
    'offline_mode' => env('POS_OFFLINE_MODE', false),
    'sync_interval' => env('POS_SYNC_INTERVAL', 300), // En secondes
    'backup_frequency' => env('POS_BACKUP_FREQUENCY', 'daily'),

    // Imprimantes et périphériques
    'default_printer' => env('POS_DEFAULT_PRINTER', ''),
    'kitchen_printer' => env('POS_KITCHEN_PRINTER', ''),
    'receipt_printer' => env('POS_RECEIPT_PRINTER', ''),
    'cash_drawer' => env('POS_CASH_DRAWER', ''),
    'barcode_scanner' => env('POS_BARCODE_SCANNER', ''),

    // Interface utilisateur
    'theme' => env('POS_THEME', 'default'),
    'language' => env('POS_LANGUAGE', 'fr'),
    'grid_size' => env('POS_GRID_SIZE', 'medium'),
    'show_images' => env('POS_SHOW_IMAGES', true),
    'button_size' => env('POS_BUTTON_SIZE', 'medium'),

    // Rapports et analytics
    'daily_reports' => env('POS_DAILY_REPORTS', true),
    'real_time_sync' => env('POS_REAL_TIME_SYNC', true),
    'analytics_enabled' => env('POS_ANALYTICS_ENABLED', true),

    // Permissions par défaut
    'default_permissions' => [
        'cashier' => [
            'process_sales',
            'view_menu',
            'apply_discount_limited',
            'process_returns_limited'
        ],
        'supervisor' => [
            'process_sales',
            'view_menu',
            'manage_menu',
            'apply_discount',
            'process_returns',
            'view_reports_limited',
            'manage_cash_drawer'
        ],
        'manager' => [
            'full_access'
        ]
    ],

    // Intégrations
    'integrations' => [
        'inventory_sync' => env('POS_INVENTORY_SYNC', true),
        'accounting_sync' => env('POS_ACCOUNTING_SYNC', false),
        'crm_sync' => env('POS_CRM_SYNC', false),
    ]
];
