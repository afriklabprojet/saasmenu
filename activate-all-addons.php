<?php

/**
 * Script pour activer TOUS les addons du système RestroSaaS
 * Usage: php activate-all-addons.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemAddons;

echo "\n╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║           ACTIVATION DE TOUS LES ADDONS RESTRO SAAS                 ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n\n";

try {
    // Liste complète des addons RestroSaaS
    $addons = [
        [
            'unique_identifier' => 'unique_slug',
            'name' => 'Domaine Personnalisé / Custom Domain',
            'description' => 'Permet aux vendors de choisir leur URL personnalisée'
        ],
        [
            'unique_identifier' => 'custom_domain',
            'name' => 'Custom Domain',
            'description' => 'Permet aux vendors d\'utiliser leur propre nom de domaine'
        ],
        [
            'unique_identifier' => 'whatsapp',
            'name' => 'WhatsApp Business Integration',
            'description' => 'Intégration WhatsApp Business API pour notifications'
        ],
        [
            'unique_identifier' => 'telegram',
            'name' => 'Telegram Integration',
            'description' => 'Intégration Telegram pour notifications'
        ],
        [
            'unique_identifier' => 'pwa',
            'name' => 'Progressive Web App (PWA)',
            'description' => 'Application Web Progressive pour installation mobile'
        ],
        [
            'unique_identifier' => 'pos',
            'name' => 'Point of Sale (POS)',
            'description' => 'Système de caisse pour ventes en magasin'
        ],
        [
            'unique_identifier' => 'loyalty',
            'name' => 'Programme de Fidélité',
            'description' => 'Système de points et récompenses pour clients fidèles'
        ],
        [
            'unique_identifier' => 'table_booking',
            'name' => 'Réservation de Tables',
            'description' => 'Système de réservation de tables en ligne'
        ],
        [
            'unique_identifier' => 'delivery',
            'name' => 'Livraison',
            'description' => 'Système de gestion des livraisons'
        ],
        [
            'unique_identifier' => 'coupon',
            'name' => 'Système de Coupons',
            'description' => 'Gestion des codes promo et réductions'
        ],
        [
            'unique_identifier' => 'blog',
            'name' => 'Blog',
            'description' => 'Publication d\'articles et actualités'
        ],
        [
            'unique_identifier' => 'google_analytics',
            'name' => 'Google Analytics',
            'description' => 'Suivi et analyse du trafic avec Google Analytics'
        ],
        [
            'unique_identifier' => 'seo',
            'name' => 'SEO Tools',
            'description' => 'Outils d\'optimisation pour moteurs de recherche'
        ],
        [
            'unique_identifier' => 'multi_language',
            'name' => 'Multi-langues',
            'description' => 'Support de plusieurs langues'
        ],
        [
            'unique_identifier' => 'social_login',
            'name' => 'Connexion Sociale',
            'description' => 'Connexion via Facebook, Google, etc.'
        ]
    ];

    echo "🔍 Recherche des addons existants...\n\n";

    $existingAddons = SystemAddons::all();
    $existingIds = $existingAddons->pluck('unique_identifier')->toArray();

    echo "📊 Addons actuels dans la base de données: " . count($existingAddons) . "\n";

    if (count($existingAddons) > 0) {
        foreach ($existingAddons as $addon) {
            $status = $addon->activated ? '✅' : '⭕';
            echo "   {$status} {$addon->unique_identifier} - {$addon->name}\n";
        }
    } else {
        echo "   ⚠️  Aucun addon trouvé\n";
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔧 ACTIVATION EN COURS...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $activated = 0;
    $created = 0;
    $alreadyActive = 0;

    foreach ($addons as $addonData) {
        $addon = SystemAddons::where('unique_identifier', $addonData['unique_identifier'])->first();

        if (!$addon) {
            // Créer le nouvel addon
            $addon = new SystemAddons();
            $addon->unique_identifier = $addonData['unique_identifier'];
            $addon->name = $addonData['name'];
            $addon->activated = 1;
            $addon->image = 'default.png';
            $addon->created_at = now();
            $addon->updated_at = now();
            $addon->save();

            echo "✨ CRÉÉ ET ACTIVÉ: {$addonData['unique_identifier']}\n";
            echo "   📝 {$addonData['name']}\n";
            echo "   💡 {$addonData['description']}\n\n";
            $created++;
        } elseif ($addon->activated == 0) {
            // Activer l'addon existant
            $addon->activated = 1;
            $addon->updated_at = now();
            $addon->save();

            echo "🔓 ACTIVÉ: {$addonData['unique_identifier']}\n";
            echo "   📝 {$addonData['name']}\n";
            echo "   💡 {$addonData['description']}\n\n";
            $activated++;
        } else {
            echo "✅ DÉJÀ ACTIF: {$addonData['unique_identifier']}\n";
            echo "   📝 {$addonData['name']}\n\n";
            $alreadyActive++;
        }
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 RÉSUMÉ\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   ✨ Addons créés: {$created}\n";
    echo "   🔓 Addons activés: {$activated}\n";
    echo "   ✅ Déjà actifs: {$alreadyActive}\n";
    echo "   📦 Total: " . ($created + $activated + $alreadyActive) . "\n\n";

    // Vérifier le total final
    $finalCount = SystemAddons::where('activated', 1)->count();
    $totalCount = SystemAddons::count();

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ ÉTAT FINAL\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   🟢 Addons actifs: {$finalCount} / {$totalCount}\n";
    echo "   🔴 Addons inactifs: " . ($totalCount - $finalCount) . "\n\n";

    if ($finalCount == $totalCount) {
        echo "   🎉 TOUS LES ADDONS SONT MAINTENANT ACTIFS!\n\n";
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🚀 FONCTIONNALITÉS ACTIVÉES\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   🌐 Domaine personnalisé (slug): localhost:8000/mon-restaurant\n";
    echo "   🌍 Custom domain: restaurant.com → votre-site.com\n";
    echo "   💬 WhatsApp Business: Notifications via WhatsApp\n";
    echo "   📱 PWA: Installation sur mobile/tablette\n";
    echo "   🏪 POS: Caisse pour ventes en magasin\n";
    echo "   🎁 Fidélité: Points et récompenses clients\n";
    echo "   🪑 Réservations: Tables réservables en ligne\n";
    echo "   🚚 Livraison: Gestion des livraisons\n";
    echo "   🎫 Coupons: Codes promo et réductions\n";
    echo "   📝 Blog: Articles et actualités\n";
    echo "   📊 Analytics: Suivi Google Analytics\n";
    echo "   🔍 SEO: Optimisation moteurs de recherche\n";
    echo "   🌍 Multi-langues: Support plusieurs langues\n";
    echo "   👥 Social Login: Connexion Facebook/Google\n\n";

    echo "💡 NOTE IMPORTANTE\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   Certains addons nécessitent une configuration supplémentaire:\n";
    echo "   • WhatsApp: Configurer l'API dans .env\n";
    echo "   • Custom Domain: Configurer DNS et certificats SSL\n";
    echo "   • Google Analytics: Ajouter le code de suivi\n";
    echo "   • Social Login: Configurer Facebook/Google Apps\n\n";
    echo "   Consultez le panel admin pour configurer chaque addon.\n\n";

} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n\n";
    exit(1);
}

echo "✨ Terminé!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "🔄 N'oubliez pas de nettoyer les caches:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan view:clear\n\n";
