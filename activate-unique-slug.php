<?php

/**
 * Script pour activer l'addon unique_slug (domaine personnalisé)
 * Usage: php activate-unique-slug.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemAddons;

echo "\n╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║        ACTIVATION DE L'ADDON DOMAINE PERSONNALISÉ                   ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n\n";

try {
    // Chercher l'addon unique_slug
    $addon = SystemAddons::where('unique_identifier', 'unique_slug')->first();

    if (!$addon) {
        echo "❌ L'addon 'unique_slug' n'existe pas dans la base de données.\n";
        echo "📋 Addons disponibles:\n\n";

        $addons = SystemAddons::all();
        foreach ($addons as $a) {
            $status = $a->activated ? '✅ Activé' : '⭕ Désactivé';
            echo "   {$status} - {$a->unique_identifier} ({$a->name})\n";
        }

        // Créer l'addon s'il n'existe pas
        echo "\n🔧 Création de l'addon unique_slug...\n";
        $addon = new SystemAddons();
        $addon->unique_identifier = 'unique_slug';
        $addon->name = 'Domaine Personnalisé / Custom Domain';
        $addon->activated = 1;
        $addon->image = 'default.png';
        $addon->created_at = now();
        $addon->updated_at = now();
        $addon->save();

        echo "✅ Addon créé et activé avec succès!\n";
    } else {
        if ($addon->activated == 1) {
            echo "ℹ️  L'addon 'unique_slug' est déjà activé.\n";
            echo "   Nom: {$addon->name}\n";
            echo "   ID: {$addon->id}\n";
        } else {
            echo "🔧 Activation de l'addon 'unique_slug'...\n";
            $addon->activated = 1;
            $addon->save();
            echo "✅ Addon activé avec succès!\n";
        }
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ RÉSULTAT\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "Le champ de domaine personnalisé apparaîtra maintenant dans le\n";
    echo "formulaire d'inscription vendor:\n\n";
    echo "   🌐 URL: http://localhost:8000/admin/register\n\n";
    echo "Les vendors pourront choisir leur slug personnalisé:\n";
    echo "   → http://localhost:8000/mon-restaurant\n";
    echo "   → http://localhost:8000/belle-jolie\n";
    echo "   → http://localhost:8000/koffi-resto\n\n";

} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n\n";
    exit(1);
}

echo "✨ Terminé!\n\n";
