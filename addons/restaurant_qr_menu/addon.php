<?php

/**
 * Restaurant QR Menu Addon
 *
 * @package RestroSaaS
 * @version 1.0.0
 * @author RestroSaaS Team
 */

return [
    'name' => 'Restaurant QR Menu',
    'slug' => 'restaurant_qr_menu',
    'version' => '1.0.0',
    'description' => 'Système de menu QR code pour restaurants sans contact',
    'author' => 'RestroSaaS Team',
    'category' => 'Restaurant Management',
    'status' => 'active',
    'dependencies' => [],
    'config' => [
        'qr_code_size' => 300,
        'qr_code_format' => 'png',
        'auto_generate' => true,
        'custom_design' => true,
        'logo_overlay' => true,
        'download_enabled' => true,
        'print_enabled' => true,
        'table_specific' => true,
        'multi_language' => true,
    ],
    'features' => [
        'Génération automatique de QR codes pour menus',
        'QR codes spécifiques par table',
        'Design personnalisable avec logo',
        'Support multilingue',
        'Interface d\'administration complète',
        'Téléchargement et impression',
        'Analytics de scan QR',
        'Gestion par restaurant/vendor',
    ],
    'routes' => [
        'admin' => 'addons/restaurant_qr_menu/routes/admin.php',
        'web' => 'addons/restaurant_qr_menu/routes/web.php',
    ],
    'views' => 'addons/restaurant_qr_menu/views',
    'migrations' => 'addons/restaurant_qr_menu/migrations',
    'controllers' => 'addons/restaurant_qr_menu/controllers',
    'models' => 'addons/restaurant_qr_menu/models',
    'assets' => 'addons/restaurant_qr_menu/assets',
];
