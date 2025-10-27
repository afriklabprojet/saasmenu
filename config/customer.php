<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Customer Account System
    |--------------------------------------------------------------------------
    |
    | This configuration controls the customer account features.
    | By default, customers order via WhatsApp without needing an account.
    | Enable this only if you want to offer optional customer accounts.
    |
    */

    'enabled' => env('CUSTOMER_ACCOUNTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Customer Dashboard Features
    |--------------------------------------------------------------------------
    |
    | Control which features are available in the customer dashboard
    |
    */

    'features' => [
        'profile' => true,
        'order_history' => true,
        'addresses' => true,
        'wishlist' => true,
        'reorder' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    |
    | Settings for customer order management
    |
    */

    'orders' => [
        'allow_cancel' => true,
        'cancel_time_limit' => 30, // minutes after order placement
        'statuses_can_cancel' => ['Pending', 'Accepted'],
    ],

];
