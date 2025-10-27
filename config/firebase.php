<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour Firebase Cloud Messaging (FCM) et autres services
    | Firebase utilisés dans l'application RestroSaaS.
    |
    */

    // Configuration principale
    'project_id' => env('FIREBASE_PROJECT_ID', ''),
    'enabled' => env('FIREBASE_ENABLED', false),

    // Authentification
    'server_key' => env('FIREBASE_SERVER_KEY', ''),
    'credentials' => env('FIREBASE_CREDENTIALS', ''),
    'web_api_key' => env('FIREBASE_WEB_API_KEY', ''),

    // URLs
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', ''),
    'app_id' => env('FIREBASE_APP_ID', ''),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID', ''),

    // Configuration FCM
    'fcm' => [
        'base_url' => 'https://fcm.googleapis.com',
        'v1_url' => 'https://fcm.googleapis.com/v1/projects',
        'iid_url' => 'https://iid.googleapis.com/iid/v1',
        'timeout' => 30,
        'max_tokens_per_request' => 1000,
    ],

    // Topics par défaut
    'default_topics' => [
        'all_users' => 'Tous les utilisateurs',
        'admins' => 'Administrateurs',
        'restaurants' => 'Restaurants',
        'customers' => 'Clients',
        'orders' => 'Notifications de commandes',
        'promotions' => 'Promotions et offres',
        'updates' => 'Mises à jour système',
    ],

    // Segments par défaut
    'default_segments' => [
        'active_users' => [
            'name' => 'Utilisateurs actifs',
            'conditions' => [
                ['field' => 'last_login_at', 'operator' => '>=', 'value' => '30 days ago']
            ]
        ],
        'new_users' => [
            'name' => 'Nouveaux utilisateurs',
            'conditions' => [
                ['field' => 'created_at', 'operator' => '>=', 'value' => '7 days ago']
            ]
        ],
        'premium_users' => [
            'name' => 'Utilisateurs premium',
            'conditions' => [
                ['field' => 'subscription_status', 'operator' => '=', 'value' => 'active']
            ]
        ],
    ],

    // Templates de notifications
    'notification_templates' => [
        'order_created' => [
            'title' => 'Nouvelle commande #{{order_id}}',
            'body' => 'Votre commande de {{total}} € a été créée avec succès.',
            'icon' => 'order',
            'action_url' => '/orders/{{order_id}}',
        ],
        'order_confirmed' => [
            'title' => 'Commande confirmée #{{order_id}}',
            'body' => 'Votre commande a été confirmée et est en préparation.',
            'icon' => 'confirmed',
            'action_url' => '/orders/{{order_id}}',
        ],
        'order_ready' => [
            'title' => 'Commande prête #{{order_id}}',
            'body' => 'Votre commande est prête pour la livraison/récupération.',
            'icon' => 'ready',
            'action_url' => '/orders/{{order_id}}',
        ],
        'payment_success' => [
            'title' => 'Paiement confirmé',
            'body' => 'Votre paiement de {{amount}} € a été traité avec succès.',
            'icon' => 'payment',
            'action_url' => '/payments/{{payment_id}}',
        ],
        'promotion' => [
            'title' => '🎉 Offre spéciale !',
            'body' => '{{promotion_text}}',
            'icon' => 'promotion',
            'action_url' => '/promotions/{{promotion_id}}',
        ],
        'welcome' => [
            'title' => 'Bienvenue {{user_name}} !',
            'body' => 'Merci de vous être inscrit. Découvrez nos délicieux plats.',
            'icon' => 'welcome',
            'action_url' => '/restaurants',
        ],
    ],

    // Automations par défaut
    'default_automations' => [
        'welcome_new_user' => [
            'name' => 'Message de bienvenue',
            'trigger_type' => 'user_registered',
            'delay_minutes' => 5,
            'template' => 'welcome',
        ],
        'order_confirmation' => [
            'name' => 'Confirmation de commande',
            'trigger_type' => 'order_created',
            'delay_minutes' => 0,
            'template' => 'order_created',
        ],
        'payment_confirmation' => [
            'name' => 'Confirmation de paiement',
            'trigger_type' => 'payment_success',
            'delay_minutes' => 0,
            'template' => 'payment_success',
        ],
    ],

    // Configuration des priorités
    'priorities' => [
        'low' => [
            'android' => ['priority' => 'normal'],
            'ios' => ['priority' => '5'],
        ],
        'normal' => [
            'android' => ['priority' => 'high'],
            'ios' => ['priority' => '10'],
        ],
        'high' => [
            'android' => ['priority' => 'high', 'notification' => ['default_sound' => true]],
            'ios' => ['priority' => '10', 'sound' => 'default'],
        ],
    ],

    // Configuration des sons
    'sounds' => [
        'default' => 'default',
        'order' => 'order_sound.wav',
        'payment' => 'payment_sound.wav',
        'promotion' => 'promotion_sound.wav',
        'alert' => 'alert_sound.wav',
    ],

    // Limites et quotas
    'limits' => [
        'max_notifications_per_hour' => 1000,
        'max_notifications_per_day' => 10000,
        'max_devices_per_user' => 5,
        'max_topics_per_device' => 100,
        'notification_ttl' => 604800, // 7 jours en secondes
        'cleanup_old_notifications_days' => 90,
        'cleanup_inactive_devices_days' => 180,
    ],

    // Configuration des webhooks
    'webhooks' => [
        'delivery_receipt' => env('FIREBASE_WEBHOOK_DELIVERY', ''),
        'open_tracking' => env('FIREBASE_WEBHOOK_OPEN', ''),
        'click_tracking' => env('FIREBASE_WEBHOOK_CLICK', ''),
        'unsubscribe' => env('FIREBASE_WEBHOOK_UNSUBSCRIBE', ''),
    ],

    // Configuration des analytics
    'analytics' => [
        'enabled' => env('FIREBASE_ANALYTICS_ENABLED', true),
        'retention_days' => 365,
        'aggregate_daily' => true,
        'track_opens' => true,
        'track_clicks' => true,
        'track_deliveries' => true,
    ],

    // Configuration de la sécurité
    'security' => [
        'validate_tokens' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000,
        ],
        'allowed_origins' => [
            'localhost',
            'your-domain.com',
        ],
    ],

    // Configuration du cache
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 heure
        'prefix' => 'firebase',
        'tags' => ['firebase', 'notifications'],
    ],

    // Configuration des logs
    'logging' => [
        'enabled' => env('FIREBASE_LOGGING_ENABLED', true),
        'level' => env('FIREBASE_LOG_LEVEL', 'info'),
        'channel' => env('FIREBASE_LOG_CHANNEL', 'stack'),
        'log_requests' => true,
        'log_responses' => true,
        'log_errors' => true,
    ],

    // Configuration du queue
    'queue' => [
        'enabled' => env('FIREBASE_QUEUE_ENABLED', true),
        'connection' => env('FIREBASE_QUEUE_CONNECTION', 'database'),
        'queue' => env('FIREBASE_QUEUE_NAME', 'firebase'),
        'retry_after' => 90,
        'max_tries' => 3,
    ],

    // Configuration de test
    'testing' => [
        'enabled' => env('FIREBASE_TESTING_ENABLED', false),
        'test_token' => env('FIREBASE_TEST_TOKEN', ''),
        'mock_responses' => env('FIREBASE_MOCK_RESPONSES', false),
    ],

    // Messages d'erreur personnalisés
    'error_messages' => [
        'invalid_token' => 'Token d\'appareil invalide',
        'invalid_topic' => 'Topic invalide',
        'invalid_condition' => 'Condition invalide',
        'quota_exceeded' => 'Quota dépassé',
        'authentication_failed' => 'Échec de l\'authentification',
        'service_unavailable' => 'Service Firebase indisponible',
    ],

    // Configuration des environnements
    'environments' => [
        'development' => [
            'dry_run' => true,
            'logging_level' => 'debug',
        ],
        'staging' => [
            'dry_run' => false,
            'logging_level' => 'info',
        ],
        'production' => [
            'dry_run' => false,
            'logging_level' => 'warning',
        ],
    ],

    // Configuration Web Push
    'web_push' => [
        'vapid_keys' => [
            'public_key' => env('FIREBASE_VAPID_PUBLIC_KEY', ''),
            'private_key' => env('FIREBASE_VAPID_PRIVATE_KEY', ''),
        ],
        'subject' => env('FIREBASE_VAPID_SUBJECT', 'mailto:admin@restrosaas.com'),
    ],

];
