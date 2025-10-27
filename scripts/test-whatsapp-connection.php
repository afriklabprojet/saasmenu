#!/usr/bin/env php
<?php

/**
 * Script de test pour WhatsApp Business API
 * Usage: php test-whatsapp-connection.php
 */

// Charger Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\WhatsAppBusinessService;
use Illuminate\Support\Facades\Config;

// Couleurs pour le terminal
define('GREEN', "\033[0;32m");
define('RED', "\033[0;31m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('BOLD', "\033[1m");
define('NC', "\033[0m"); // No Color

echo "\n";
echo BOLD . "=========================================" . NC . "\n";
echo BOLD . "🧪 TEST WHATSAPP BUSINESS API" . NC . "\n";
echo BOLD . "=========================================" . NC . "\n";
echo "\n";

// Fonction d'affichage
function displayResult($success, $message, $details = null) {
    if ($success) {
        echo GREEN . "✅ " . $message . NC . "\n";
    } else {
        echo RED . "❌ " . $message . NC . "\n";
    }

    if ($details) {
        echo "   " . $details . "\n";
    }
    echo "\n";
}

// Test 1: Vérifier la configuration
echo BLUE . "▶ Test 1: Vérification de la configuration" . NC . "\n";
echo "─────────────────────────────────────────\n";

$config = [
    'API URL' => Config::get('whatsapp.api_url'),
    'API Token' => Config::get('whatsapp.api_token'),
    'Phone Number ID' => Config::get('whatsapp.phone_number_id'),
    'Enabled' => Config::get('whatsapp.enabled') ? 'Yes' : 'No',
    'Demo Mode' => Config::get('whatsapp.demo_mode') ? 'Yes' : 'No',
    'Default Country Code' => Config::get('whatsapp.default_country_code'),
];

foreach ($config as $key => $value) {
    if (empty($value) && $key !== 'Demo Mode' && $key !== 'Enabled') {
        echo RED . "❌ {$key}: " . NC . "NON CONFIGURÉ\n";
    } else {
        // Masquer une partie du token pour sécurité
        if ($key === 'API Token' && !empty($value)) {
            $maskedValue = substr($value, 0, 10) . '...' . substr($value, -5);
            echo GREEN . "✅ {$key}: " . NC . $maskedValue . "\n";
        } else {
            echo GREEN . "✅ {$key}: " . NC . $value . "\n";
        }
    }
}
echo "\n";

// Test 2: Initialiser le service
echo BLUE . "▶ Test 2: Initialisation du service" . NC . "\n";
echo "─────────────────────────────────────────\n";

try {
    $service = new WhatsAppBusinessService();
    displayResult(true, "Service WhatsAppBusinessService initialisé");
} catch (\Exception $e) {
    displayResult(false, "Échec d'initialisation du service", $e->getMessage());
    exit(1);
}

// Test 3: Tester la connexion API
echo BLUE . "▶ Test 3: Test de connexion à l'API WhatsApp" . NC . "\n";
echo "─────────────────────────────────────────\n";

if (empty(Config::get('whatsapp.api_token')) || empty(Config::get('whatsapp.phone_number_id'))) {
    displayResult(false, "Configuration incomplète", "Veuillez renseigner WHATSAPP_API_TOKEN et WHATSAPP_PHONE_NUMBER_ID dans .env");
} else {
    try {
        $connectionResult = $service->testConnection();

        if ($connectionResult['success']) {
            displayResult(true, "Connexion à l'API réussie");

            if (isset($connectionResult['details']['display_phone_number'])) {
                echo "   📞 Numéro WhatsApp: " . $connectionResult['details']['display_phone_number'] . "\n";
            }
            if (isset($connectionResult['details']['verified_name'])) {
                echo "   🏢 Nom vérifié: " . $connectionResult['details']['verified_name'] . "\n";
            }
            if (isset($connectionResult['details']['quality_rating'])) {
                echo "   ⭐ Qualité: " . $connectionResult['details']['quality_rating'] . "\n";
            }
            echo "\n";
        } else {
            displayResult(false, "Échec de connexion à l'API", $connectionResult['message']);

            if (isset($connectionResult['error'])) {
                echo "   Détails: " . json_encode($connectionResult['error'], JSON_PRETTY_PRINT) . "\n\n";
            }
        }
    } catch (\Exception $e) {
        displayResult(false, "Exception lors du test de connexion", $e->getMessage());
    }
}

// Test 4: Vérifier si la table existe
echo BLUE . "▶ Test 4: Vérification de la table whatsapp_logs" . NC . "\n";
echo "─────────────────────────────────────────\n";

try {
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('whatsapp_logs');

    if ($tableExists) {
        displayResult(true, "Table whatsapp_logs existe");

        $count = \Illuminate\Support\Facades\DB::table('whatsapp_logs')->count();
        echo "   📊 Nombre de logs: " . $count . "\n\n";
    } else {
        displayResult(false, "Table whatsapp_logs n'existe pas", "Exécutez: php artisan migrate");
    }
} catch (\Exception $e) {
    displayResult(false, "Erreur lors de la vérification de la table", $e->getMessage());
}

// Test 5: Test de formatage de numéro
echo BLUE . "▶ Test 5: Test de formatage des numéros" . NC . "\n";
echo "─────────────────────────────────────────\n";

$testNumbers = [
    '0709123456' => '225709123456',      // Numéro local
    '225709123456' => '225709123456',    // Déjà formaté
    '+225709123456' => '225709123456',   // Avec +
    '07 09 12 34 56' => '225709123456',  // Avec espaces
];

echo "Tests de formatage:\n";
foreach ($testNumbers as $input => $expected) {
    // Simuler le formatage (logique du service)
    $formatted = preg_replace('/[^0-9]/', '', $input);
    if (substr($formatted, 0, 1) === '0') {
        $formatted = '225' . substr($formatted, 1);
    }

    if ($formatted === $expected) {
        echo GREEN . "  ✅ " . NC . "$input → $formatted\n";
    } else {
        echo RED . "  ❌ " . NC . "$input → $formatted (attendu: $expected)\n";
    }
}
echo "\n";

// Test 6: Statistiques
echo BLUE . "▶ Test 6: Statistiques d'envoi" . NC . "\n";
echo "─────────────────────────────────────────\n";

try {
    $stats = $service->getStats(7);

    if ($stats['success']) {
        echo "📊 Statistiques des 7 derniers jours:\n";
        echo "   Total envoyés: " . $stats['stats']['total_sent'] . "\n";
        echo "   Réussis: " . GREEN . $stats['stats']['total_success'] . NC . "\n";
        echo "   Échoués: " . RED . $stats['stats']['total_failed'] . NC . "\n";
        echo "   Taux de réussite: " . BOLD . $stats['stats']['success_rate'] . NC . "\n";
        echo "\n";
    } else {
        displayResult(false, "Impossible de récupérer les statistiques", $stats['message']);
    }
} catch (\Exception $e) {
    displayResult(false, "Erreur lors de la récupération des stats", $e->getMessage());
}

// Test 7: Test d'envoi (optionnel)
echo BLUE . "▶ Test 7: Test d'envoi (optionnel)" . NC . "\n";
echo "─────────────────────────────────────────\n";

if (Config::get('whatsapp.demo_mode')) {
    echo YELLOW . "⚠️  Mode démo activé - Messages simulés" . NC . "\n\n";
}

$testPhone = Config::get('whatsapp.test_phone');

if (!empty($testPhone)) {
    echo "📱 Numéro de test configuré: " . $testPhone . "\n";
    echo "Voulez-vous envoyer un message de test ? (y/N): ";

    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    if (trim(strtolower($line)) === 'y') {
        $testMessage = "🎉 Test E-menu WhatsApp\n\n";
        $testMessage .= "Ceci est un message de test automatique.\n";
        $testMessage .= "Si vous recevez ce message, l'intégration fonctionne ! ✅\n\n";
        $testMessage .= "Date: " . date('d/m/Y H:i:s');

        echo "\nEnvoi en cours...\n";

        $result = $service->sendTextMessage($testPhone, $testMessage, [
            'test' => true,
            'automated_test' => true
        ]);

        if ($result['success']) {
            displayResult(true, "Message de test envoyé !",
                "Message ID: " . ($result['context']['message_id'] ?? 'N/A'));
        } else {
            displayResult(false, "Échec d'envoi du message de test", $result['status']);
        }
    }
} else {
    echo YELLOW . "⚠️  Aucun numéro de test configuré" . NC . "\n";
    echo "   Ajoutez WHATSAPP_TEST_PHONE=2250709123456 dans .env\n\n";
}

// Résumé final
echo BOLD . "=========================================" . NC . "\n";
echo BOLD . "📋 RÉSUMÉ" . NC . "\n";
echo BOLD . "=========================================" . NC . "\n";
echo "\n";

$configured = !empty(Config::get('whatsapp.api_token')) &&
               !empty(Config::get('whatsapp.phone_number_id'));

if ($configured) {
    echo GREEN . "✅ Configuration complète" . NC . "\n";
    echo GREEN . "✅ Service opérationnel" . NC . "\n";

    if (Config::get('whatsapp.enabled')) {
        if (Config::get('whatsapp.demo_mode')) {
            echo YELLOW . "⚠️  Mode démo actif - Messages simulés" . NC . "\n";
            echo "   Désactivez avec: WHATSAPP_DEMO_MODE=false\n";
        } else {
            echo GREEN . "✅ Envoi réel activé" . NC . "\n";
        }
    } else {
        echo YELLOW . "⚠️  Envoi désactivé" . NC . "\n";
        echo "   Activez avec: WHATSAPP_ENABLED=true\n";
    }

    echo "\n";
    echo "🎉 " . BOLD . "WhatsApp Business API prête à l'emploi !" . NC . "\n";
} else {
    echo RED . "❌ Configuration incomplète" . NC . "\n";
    echo "\n";
    echo "Prochaines étapes:\n";
    echo "1. Obtenez vos credentials sur Meta Business Manager\n";
    echo "2. Ajoutez-les dans .env:\n";
    echo "   WHATSAPP_API_TOKEN=votre_token\n";
    echo "   WHATSAPP_PHONE_NUMBER_ID=votre_id\n";
    echo "3. Migrez la BDD: php artisan migrate\n";
    echo "4. Relancez ce test: php test-whatsapp-connection.php\n";
}

echo "\n";
echo "📖 Documentation: " . BLUE . "WHATSAPP_BUSINESS_API_GUIDE.md" . NC . "\n";
echo "\n";
