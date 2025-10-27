<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration WhatsApp Business API via Meta (Facebook).
    | Obtenez vos credentials sur: https://business.facebook.com/
    |
    */

    /**
     * URL de base de l'API WhatsApp Business
     * Version actuelle: v18.0
     */
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),

    /**
     * Token d'accès pour l'API WhatsApp Business
     * À obtenir depuis Meta Business Manager
     */
    'api_token' => env('WHATSAPP_API_TOKEN', ''),

    /**
     * ID du numéro de téléphone WhatsApp Business
     * Format: numérique uniquement
     */
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),

    /**
     * ID du compte WhatsApp Business
     */
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', ''),

    /**
     * App Secret pour la validation des webhooks
     * Utilisé pour signer les requêtes webhook
     */
    'app_secret' => env('WHATSAPP_APP_SECRET', ''),

    /**
     * Token de vérification pour les webhooks
     * Peut être n'importe quelle chaîne aléatoire sécurisée
     */
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'emenu_whatsapp_2024'),

    /**
     * URL du webhook pour recevoir les notifications WhatsApp
     * Exemple: https://votre-domaine.com/api/whatsapp/webhook
     */
    'webhook_url' => env('WHATSAPP_WEBHOOK_URL', ''),

    /**
     * Code pays par défaut pour la Côte d'Ivoire
     * Utilisé pour formater les numéros locaux
     */
    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '225'),

    /**
     * Numéro de téléphone pour les tests
     * Format international sans le +
     */
    'test_phone' => env('WHATSAPP_TEST_PHONE', ''),

    /**
     * Timeout pour les requêtes API (en secondes)
     */
    'timeout' => env('WHATSAPP_TIMEOUT', 30),

    /**
     * Activer/Désactiver l'envoi de messages WhatsApp
     * Utile pour le développement
     */
    'enabled' => env('WHATSAPP_ENABLED', true),

    /**
     * Mode démo - Les messages sont loggés mais pas envoyés
     */
    'demo_mode' => env('WHATSAPP_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Configuration des Templates de Messages
    |--------------------------------------------------------------------------
    |
    | IDs des templates approuvés par Meta pour l'envoi de messages
    | Les templates doivent être créés et approuvés dans Meta Business Manager
    |
    */

    'templates' => [
        /**
         * Template pour la notification de nouvelle commande
         */
        'new_order' => [
            'name' => env('WHATSAPP_TEMPLATE_NEW_ORDER', 'new_order_notification'),
            'language' => 'fr',
        ],

        /**
         * Template pour la confirmation de paiement
         */
        'payment_confirmation' => [
            'name' => env('WHATSAPP_TEMPLATE_PAYMENT_CONFIRMATION', 'payment_confirmation'),
            'language' => 'fr',
        ],

        /**
         * Template pour la mise à jour de livraison
         */
        'delivery_update' => [
            'name' => env('WHATSAPP_TEMPLATE_DELIVERY_UPDATE', 'delivery_update'),
            'language' => 'fr',
        ],

        /**
         * Template pour les rappels
         */
        'reminder' => [
            'name' => env('WHATSAPP_TEMPLATE_REMINDER', 'order_reminder'),
            'language' => 'fr',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration des Notifications
    |--------------------------------------------------------------------------
    |
    | Définir quels types de notifications envoyer automatiquement
    |
    */

    'notifications' => [
        /**
         * Envoyer une notification au restaurant à chaque nouvelle commande
         */
        'notify_restaurant_on_order' => env('WHATSAPP_NOTIFY_RESTAURANT_ON_ORDER', true),

        /**
         * Envoyer une confirmation au client après paiement
         */
        'notify_customer_on_payment' => env('WHATSAPP_NOTIFY_CUSTOMER_ON_PAYMENT', true),

        /**
         * Envoyer des mises à jour de livraison au client
         */
        'notify_customer_on_delivery_update' => env('WHATSAPP_NOTIFY_CUSTOMER_ON_DELIVERY_UPDATE', true),

        /**
         * Envoyer un message de bienvenue aux nouveaux clients
         */
        'send_welcome_message' => env('WHATSAPP_SEND_WELCOME_MESSAGE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration des Limites et Retry
    |--------------------------------------------------------------------------
    */

    'limits' => [
        /**
         * Nombre maximum de tentatives d'envoi en cas d'échec
         */
        'max_retry_attempts' => 3,

        /**
         * Délai entre les tentatives (en secondes)
         */
        'retry_delay' => 60,

        /**
         * Nombre maximum de messages par minute (rate limiting)
         */
        'max_messages_per_minute' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration du Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        /**
         * Activer le logging des messages envoyés
         */
        'enabled' => true,

        /**
         * Conserver les logs pendant X jours
         */
        'retention_days' => 90,

        /**
         * Logger tous les messages ou seulement les erreurs
         */
        'log_all_messages' => true,
    ],

];
