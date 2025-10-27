<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Message Templates
    |--------------------------------------------------------------------------
    |
    | Templates de messages WhatsApp pour les différents statuts de commande.
    | Variables disponibles : {customer_name}, {order_number}, {total}, etc.
    |
    */

    'templates' => [

        /**
         * Message de nouvelle commande (envoyé au client)
         */
        'new_order' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_created',
            'template' => "🎉 *Nouvelle Commande* 🎉\n═══════════════════════════\n\nBonjour *{customer_name}* !\n\nVotre commande *#{order_number}* a été enregistrée.\n\n📦 *Détails* :\n• Restaurant: {store_name}\n• Total: {grand_total}\n• Type: {delivery_type}\n• Livraison: {date} à {time}\n\n📱 *Suivi* :\n{track_order_url}\n\nMerci de votre confiance ! 🙏",
        ],

        /**
         * Message de confirmation (restaurant a accepté)
         */
        'order_confirmed' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_accepted',
            'template' => "✅ *Commande Confirmée* ✅\n\nBonjour *{customer_name}* !\n\nBonne nouvelle ! Votre commande *#{order_number}* a été confirmée par {store_name}.\n\n⏱️ *Temps estimé* : 25-35 minutes\n📍 *Suivi* : {track_order_url}\n\nÀ bientôt ! 😊",
        ],

        /**
         * Message de préparation en cours
         */
        'order_preparing' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_preparing',
            'template' => "👨‍🍳 *Préparation en Cours* 👨‍🍳\n\nBonjour *{customer_name}* !\n\nNotre chef prépare votre commande *#{order_number}* avec soin.\n\n⏱️ Vous serez notifié dès qu'elle sera prête.\n📍 Suivi : {track_order_url}\n\nPatience, ça vaut le coup ! 😋",
        ],

        /**
         * Message de commande prête
         */
        'order_ready' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_ready',
            'template' => "✨ *Commande Prête* ✨\n\nBonjour *{customer_name}* !\n\nVotre commande *#{order_number}* est prête !\n\n{delivery_info}\n\nBon appétit ! 🍽️",
        ],

        /**
         * Message de livraison en cours
         */
        'order_on_way' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_on_way',
            'template' => "🚗 *Livraison en Route* 🚗\n\nBonjour *{customer_name}* !\n\nVotre commande *#{order_number}* est en route !\n\n📍 Adresse : {address}\n📱 Restez disponible au : {customer_mobile}\n\n⏱️ Livraison estimée : 10-15 minutes\n\nÀ tout de suite ! 😊",
        ],

        /**
         * Message de livraison effectuée
         */
        'order_delivered' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_delivered',
            'template' => "🎊 *Livraison Effectuée* 🎊\n\nBonjour *{customer_name}* !\n\nVotre commande *#{order_number}* a été livrée.\n\nMerci d'avoir choisi {store_name} !\n\n⭐ Votre avis nous intéresse :\n{store_url}\n\nÀ très bientôt ! 🙏",
        ],

        /**
         * Message d'annulation
         */
        'order_cancelled' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'order_cancelled',
            'template' => "❌ *Commande Annulée* ❌\n\nBonjour *{customer_name}*,\n\nVotre commande *#{order_number}* a été annulée.\n\n{cancellation_reason}\n\nNous sommes désolés pour ce désagrément.\n\n📱 Contactez-nous : {store_mobile}\n\nÀ bientôt ! 🙏",
        ],

        /**
         * Rappel de paiement
         */
        'payment_reminder' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'payment_pending',
            'template' => "💳 *Rappel de Paiement* 💳\n\nBonjour *{customer_name}*,\n\nVotre commande *#{order_number}* est en attente de paiement.\n\n💰 *Montant* : {grand_total}\n\n{payment_link}\n\nBesoin d'aide ?\n☎️ {store_mobile}",
        ],

        /**
         * Message de bienvenue (chat WhatsApp)
         */
        'welcome_message' => [
            'enabled' => true,
            'send_to' => 'customer',
            'trigger' => 'chat_initiated',
            'template' => "👋 *Bienvenue chez {store_name}* !\n\nComment puis-je vous aider aujourd'hui ?\n\n🍽️ *Nos services* :\n1️⃣ Consulter le menu\n2️⃣ Passer une commande\n3️⃣ Suivre ma commande\n4️⃣ Nous contacter\n\nRépondez avec le numéro de votre choix.",
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Variables Disponibles
    |--------------------------------------------------------------------------
    |
    | Liste des variables utilisables dans les templates
    |
    */

    'variables' => [
        '{customer_name}' => 'Nom du client',
        '{customer_mobile}' => 'Téléphone du client',
        '{order_number}' => 'Numéro de commande',
        '{store_name}' => 'Nom du restaurant',
        '{store_mobile}' => 'Téléphone du restaurant',
        '{delivery_type}' => 'Type de livraison (Livraison/Retrait)',
        '{date}' => 'Date de livraison',
        '{time}' => 'Heure de livraison',
        '{sub_total}' => 'Sous-total',
        '{total_tax}' => 'Total des taxes',
        '{delivery_charge}' => 'Frais de livraison',
        '{discount_amount}' => 'Montant de réduction',
        '{grand_total}' => 'Total général',
        '{payment_type}' => 'Mode de paiement',
        '{address}' => 'Adresse complète',
        '{building}' => 'Bâtiment',
        '{landmark}' => 'Point de repère',
        '{postal_code}' => 'Code postal',
        '{notes}' => 'Notes de commande',
        '{track_order_url}' => 'URL de suivi',
        '{store_url}' => 'URL du restaurant',
        '{payment_link}' => 'Lien de paiement',
        '{item_variable}' => 'Liste des articles',
        '{delivery_info}' => 'Informations de livraison',
        '{cancellation_reason}' => 'Raison d\'annulation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration de Format
    |--------------------------------------------------------------------------
    */

    'formatting' => [
        'use_emojis' => true,
        'use_bold' => true, // *texte*
        'use_italic' => false, // _texte_
        'use_strikethrough' => false, // ~texte~
        'use_monospace' => false, // ```texte```
        'line_separator' => '═',
        'section_separator' => '─',
        'max_length' => 4096, // Limite WhatsApp
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Automatiques
    |--------------------------------------------------------------------------
    */

    'auto_notifications' => [
        'order_created' => env('WHATSAPP_AUTO_NOTIFY_ORDER_CREATED', true),
        'order_accepted' => env('WHATSAPP_AUTO_NOTIFY_ORDER_ACCEPTED', true),
        'order_preparing' => env('WHATSAPP_AUTO_NOTIFY_ORDER_PREPARING', true),
        'order_ready' => env('WHATSAPP_AUTO_NOTIFY_ORDER_READY', true),
        'order_on_way' => env('WHATSAPP_AUTO_NOTIFY_ORDER_ON_WAY', true),
        'order_delivered' => env('WHATSAPP_AUTO_NOTIFY_ORDER_DELIVERED', true),
        'order_cancelled' => env('WHATSAPP_AUTO_NOTIFY_ORDER_CANCELLED', true),
        'payment_pending' => env('WHATSAPP_AUTO_NOTIFY_PAYMENT_PENDING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Délais de Notification
    |--------------------------------------------------------------------------
    |
    | Délais en minutes avant d'envoyer certaines notifications
    |
    */

    'notification_delays' => [
        'payment_reminder' => 15, // 15 min après la commande
        'preparation_update' => 10, // 10 min après acceptation
        'delivery_eta' => 5, // 5 min avant livraison estimée
    ],

    /*
    |--------------------------------------------------------------------------
    | Langue par Défaut
    |--------------------------------------------------------------------------
    */

    'default_language' => env('WHATSAPP_DEFAULT_LANGUAGE', 'fr'),

    /*
    |--------------------------------------------------------------------------
    | Support Multilingue
    |--------------------------------------------------------------------------
    */

    'languages' => [
        'fr' => 'Français',
        'en' => 'English',
        'ar' => 'العربية',
    ],

];
