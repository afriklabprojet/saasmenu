<?php

/**
 * Script pour vérifier l'implémentation des addons
 * Usage: php check-addons-implementation.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemAddons;

echo "\n╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║         VÉRIFICATION DE L'IMPLÉMENTATION DES ADDONS                 ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n\n";

// Liste des addons activés
$addons = SystemAddons::where('activated', 1)->get();

echo "📊 ADDONS ACTIFS: " . $addons->count() . "\n\n";

$implemented = [];
$notImplemented = [];
$partiallyImplemented = [];

// Vérification de chaque addon
foreach ($addons as $addon) {
    $id = $addon->unique_identifier;

    // Compter les fichiers qui mentionnent cet addon
    $searchCommand = "grep -r -l '{$id}' app/ resources/ routes/ config/ 2>/dev/null | wc -l";
    $fileCount = (int)trim(shell_exec($searchCommand));

    // Vérifier si un contrôleur spécifique existe
    $hasController = file_exists(__DIR__ . "/app/Http/Controllers/admin/" . ucfirst(str_replace('_', '', $id)) . "Controller.php") ||
                     file_exists(__DIR__ . "/app/Http/Controllers/" . ucfirst(str_replace('_', '', $id)) . "Controller.php");

    // Vérifier s'il y a des vues
    $hasViews = is_dir(__DIR__ . "/resources/views/admin/" . str_replace('_', '-', $id));

    // Catégoriser
    if ($fileCount > 10 || $hasController || $hasViews) {
        $implemented[] = [
            'id' => $id,
            'name' => $addon->name,
            'files' => $fileCount,
            'controller' => $hasController,
            'views' => $hasViews
        ];
    } elseif ($fileCount > 0 && $fileCount <= 10) {
        $partiallyImplemented[] = [
            'id' => $id,
            'name' => $addon->name,
            'files' => $fileCount
        ];
    } else {
        $notImplemented[] = [
            'id' => $id,
            'name' => $addon->name
        ];
    }
}

// Affichage des résultats
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ ADDONS ENTIÈREMENT IMPLÉMENTÉS (" . count($implemented) . ")\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (count($implemented) > 0) {
    foreach ($implemented as $addon) {
        echo "✅ {$addon['id']}\n";
        echo "   📝 {$addon['name']}\n";
        echo "   📁 Fichiers: {$addon['files']}\n";
        echo "   🎮 Controller: " . ($addon['controller'] ? 'Oui' : 'Non') . "\n";
        echo "   👁️  Vues: " . ($addon['views'] ? 'Oui' : 'Non') . "\n\n";
    }
} else {
    echo "   Aucun addon entièrement implémenté.\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "⚠️  ADDONS PARTIELLEMENT IMPLÉMENTÉS (" . count($partiallyImplemented) . ")\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (count($partiallyImplemented) > 0) {
    foreach ($partiallyImplemented as $addon) {
        echo "⚠️  {$addon['id']}\n";
        echo "   📝 {$addon['name']}\n";
        echo "   📁 Fichiers trouvés: {$addon['files']}\n";
        echo "   💡 Nécessite plus d'implémentation\n\n";
    }
} else {
    echo "   Aucun addon partiellement implémenté.\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "❌ ADDONS NON IMPLÉMENTÉS (" . count($notImplemented) . ")\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (count($notImplemented) > 0) {
    foreach ($notImplemented as $addon) {
        echo "❌ {$addon['id']}\n";
        echo "   📝 {$addon['name']}\n";
        echo "   💡 Aucun fichier trouvé - Addon non implémenté\n\n";
    }
} else {
    echo "   🎉 Tous les addons activés sont implémentés!\n\n";
}

// Résumé global
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 RÉSUMÉ GLOBAL\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$total = count($addons);
$fullyImplemented = count($implemented);
$partial = count($partiallyImplemented);
$notImpl = count($notImplemented);
$percentage = $total > 0 ? round(($fullyImplemented / $total) * 100, 1) : 0;

echo "   Total d'addons actifs: {$total}\n";
echo "   ✅ Entièrement implémentés: {$fullyImplemented} ({$percentage}%)\n";
echo "   ⚠️  Partiellement implémentés: {$partial}\n";
echo "   ❌ Non implémentés: {$notImpl}\n\n";

if ($percentage == 100) {
    echo "   🎉 EXCELLENT! Tous les addons sont implémentés!\n\n";
} elseif ($percentage >= 80) {
    echo "   👍 TRÈS BIEN! La plupart des addons sont implémentés.\n\n";
} elseif ($percentage >= 50) {
    echo "   ⚠️  MOYEN. Plusieurs addons nécessitent une implémentation.\n\n";
} else {
    echo "   ⚠️  ATTENTION! Beaucoup d'addons ne sont pas implémentés.\n\n";
}

// Recommandations
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "💡 RECOMMANDATIONS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (count($notImplemented) > 0) {
    echo "   Pour les addons non implémentés:\n";
    echo "   1. Désactivez-les s'ils ne sont pas nécessaires\n";
    echo "   2. Ou implémentez-les avant de les utiliser en production\n\n";
}

if (count($partiallyImplemented) > 0) {
    echo "   Pour les addons partiellement implémentés:\n";
    echo "   1. Vérifiez leur fonctionnalité\n";
    echo "   2. Complétez l'implémentation si nécessaire\n";
    echo "   3. Testez-les avant la production\n\n";
}

echo "   Configuration recommandée:\n";
echo "   → Gardez actifs: unique_slug, custom_domain, pwa\n";
echo "   → Configurez si nécessaire: whatsapp, google_analytics\n";
echo "   → Désactivez si non utilisés: telegram, social_login\n\n";

echo "✨ Analyse terminée!\n\n";
