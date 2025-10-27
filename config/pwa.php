<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Configuration for PWA Push Notifications
    |--------------------------------------------------------------------------
    |
    | VAPID (Voluntary Application Server Identification) keys are used
    | to identify your server when sending push notifications.
    | 
    | Generate VAPID keys using: npx web-push generate-vapid-keys
    | Or online at: https://vapidkeys.com/
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@e-menu.com'),
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Notification Settings
    |--------------------------------------------------------------------------
    */
    
    'defaults' => [
        'ttl' => 2419200, // 4 semaines en secondes
        'urgency' => 'normal', // very-low, low, normal, high
        'topic' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Icons and Assets
    |--------------------------------------------------------------------------
    */
    
    'assets' => [
        'icon' => env('PWA_DEFAULT_ICON', '/images/logo.png'),
        'badge' => env('PWA_DEFAULT_BADGE', '/images/badge.png'),
        'sound' => env('PWA_DEFAULT_SOUND', '/sounds/notification.mp3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Cleanup
    |--------------------------------------------------------------------------
    |
    | Automatically remove old push subscriptions
    |
    */
    
    'cleanup' => [
        'enabled' => env('PWA_CLEANUP_ENABLED', true),
        'days' => env('PWA_CLEANUP_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    
    'rate_limit' => [
        'enabled' => env('PWA_RATE_LIMIT_ENABLED', true),
        'max_per_user_per_hour' => env('PWA_MAX_NOTIFICATIONS_PER_USER_PER_HOUR', 10),
        'max_global_per_minute' => env('PWA_MAX_NOTIFICATIONS_GLOBAL_PER_MINUTE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    
    'retry' => [
        'max_attempts' => env('PWA_MAX_RETRY_ATTEMPTS', 3),
        'delay_seconds' => env('PWA_RETRY_DELAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    
    'logging' => [
        'enabled' => env('PWA_LOGGING_ENABLED', true),
        'channel' => env('PWA_LOG_CHANNEL', 'daily'),
        'level' => env('PWA_LOG_LEVEL', 'info'),
    ],
];