<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CinetPay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CinetPay payment gateway integration
    |
    */

    'api_url' => [
        'sandbox' => 'https://api-checkout.cinetpay.com/v2/payment',
        'live' => 'https://api-checkout.cinetpay.com/v2/payment',
    ],

    'check_url' => [
        'sandbox' => 'https://api-checkout.cinetpay.com/v2/payment/check',
        'live' => 'https://api-checkout.cinetpay.com/v2/payment/check',
    ],

    'supported_currencies' => [
        'XOF', // West African CFA Franc
        'XAF', // Central African CFA Franc  
        'CDF', // Congolese Franc
        'GNF', // Guinean Franc
        'USD', // US Dollar
        'EUR', // Euro
    ],

    'supported_countries' => [
        'BF' => 'Burkina Faso',
        'CI' => 'Côte d\'Ivoire',
        'ML' => 'Mali',
        'NE' => 'Niger',
        'SN' => 'Senegal',
        'TG' => 'Togo',
        'BJ' => 'Benin',
        'CM' => 'Cameroon',
        'CD' => 'Congo (DRC)',
        'GN' => 'Guinea',
    ],

    'payment_methods' => [
        'MOBILE_MONEY' => 'Mobile Money',
        'CREDIT_CARD' => 'Credit/Debit Card',
        'FLOOZ' => 'Flooz',
        'TMONEY' => 'T-Money',
        'WIZALL' => 'Wizall',
        'ORANGE_MONEY' => 'Orange Money',
        'MTN_MONEY' => 'MTN Mobile Money',
        'MOOV_MONEY' => 'Moov Money',
        'WAVE' => 'Wave',
    ],

    'webhook_timeout' => 30, // seconds

    'default_description' => 'Payment for order from RestroSaaS',
];