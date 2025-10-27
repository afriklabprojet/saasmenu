#!/bin/bash

# Script de validation finale pour RestroSaaS Addons
# Vérification complète du système après résolution des erreurs

echo "🔍 RestroSaaS Addons - Validation Finale"
echo "========================================"
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ERRORS=0
TESTS_PASSED=0
TESTS_FAILED=0

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
    ((TESTS_PASSED++))
}

print_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
    ((TESTS_FAILED++))
    ((ERRORS++))
}

print_step "1. Test de l'autoloader et des classes"

# Test des services
if php artisan tinker --execute="app('App\Services\QRCodeService'); echo 'SUCCESS';" 2>/dev/null | grep -q "SUCCESS"; then
    print_success "Service QRCodeService chargé avec succès"
else
    print_error "Échec du chargement du service QRCodeService"
fi

if php artisan tinker --execute="app('App\Services\FirebaseService'); echo 'SUCCESS';" 2>/dev/null | grep -q "SUCCESS"; then
    print_success "Service FirebaseService chargé avec succès"
else
    print_error "Échec du chargement du service FirebaseService"
fi

if php artisan tinker --execute="app('App\Services\ImportExportService'); echo 'SUCCESS';" 2>/dev/null | grep -q "SUCCESS"; then
    print_success "Service ImportExportService chargé avec succès"
else
    print_error "Échec du chargement du service ImportExportService"
fi

print_step "2. Test des packages externes"

# Test SimpleSoftwareIO QrCode
if php artisan tinker --execute="use SimpleSoftwareIO\QrCode\Facades\QrCode; echo 'SUCCESS';" 2>/dev/null | grep -q "SUCCESS"; then
    print_success "Package SimpleSoftwareIO/QrCode chargé avec succès"
else
    print_error "Échec du chargement du package QrCode"
fi

print_step "3. Test des modèles Addon"

MODELS=("POSTerminal" "POSSession" "LoyaltyProgram" "LoyaltyMember" "TableQrCode" "DeviceToken" "ImportJob" "ExportJob")
for model in "${MODELS[@]}"; do
    if php artisan tinker --execute="try { App\\Models\\$model::query(); echo 'SUCCESS'; } catch(Exception \$e) { echo 'FAILED'; }" 2>/dev/null | grep -q "SUCCESS"; then
        print_success "Modèle '$model' accessible"
    else
        print_error "Modèle '$model' non accessible"
    fi
done

print_step "4. Test des contrôleurs API"

CONTROLLERS=("PosApiController" "LoyaltyApiController" "TableQrApiController" "ApiDocumentationController")
for controller in "${CONTROLLERS[@]}"; do
    if find app/Http/Controllers -name "$controller.php" | grep -q "$controller.php"; then
        print_success "Contrôleur '$controller' trouvé"
    else
        print_error "Contrôleur '$controller' manquant"
    fi
done

print_step "5. Test des middlewares"

MIDDLEWARES=("ValidateAddonPermission" "AddonRateLimit" "ValidateApiKey")
for middleware in "${MIDDLEWARES[@]}"; do
    if find app/Http/Middleware -name "$middleware.php" | grep -q "$middleware.php"; then
        print_success "Middleware '$middleware' trouvé"
    else
        print_error "Middleware '$middleware' manquant"
    fi
done

print_step "6. Test des commandes Artisan"

COMMANDS=("import-export:process-import" "import-export:process-export" "import-export:cleanup" "firebase:send-notification")
for command in "${COMMANDS[@]}"; do
    if php artisan list 2>/dev/null | grep -q "$command"; then
        print_success "Commande '$command' enregistrée"
    else
        print_error "Commande '$command' non enregistrée"
    fi
done

print_step "7. Test des fichiers de configuration"

CONFIG_FILES=("l5-swagger.php" "addon-queue.php")
for config in "${CONFIG_FILES[@]}"; do
    if [ -f "config/$config" ]; then
        print_success "Fichier de configuration '$config' trouvé"
    else
        print_error "Fichier de configuration '$config' manquant"
    fi
done

print_step "8. Test des répertoires addon"

ADDON_DIRS=("storage/app/imports" "storage/app/exports" "storage/app/firebase" "storage/app/qr-codes")
for dir in "${ADDON_DIRS[@]}"; do
    if [ -d "$dir" ] && [ -w "$dir" ]; then
        print_success "Répertoire addon '$dir' accessible en écriture"
    else
        print_error "Répertoire addon '$dir' manquant ou non accessible en écriture"
    fi
done

print_step "9. Test des factories"

FACTORIES=("POSTerminalFactory" "LoyaltyProgramFactory" "TableQrCodeFactory" "DeviceTokenFactory")
for factory in "${FACTORIES[@]}"; do
    if [ -f "database/factories/$factory.php" ]; then
        print_success "Factory '$factory' trouvée"
    else
        print_error "Factory '$factory' manquante"
    fi
done

print_step "10. Test des seeders"

if [ -f "database/seeders/AddonDemoSeeder.php" ]; then
    print_success "Seeder AddonDemoSeeder trouvé"
else
    print_error "Seeder AddonDemoSeeder manquant"
fi

print_step "11. Test des scripts de gestion"

SCRIPTS=("quick-start.sh" "validate-addons.sh" "setup-production.sh" "deploy-addons.sh" "fix-namespaces.sh")
for script in "${SCRIPTS[@]}"; do
    if [ -f "$script" ] && [ -x "$script" ]; then
        print_success "Script '$script' trouvé et exécutable"
    else
        print_error "Script '$script' manquant ou non exécutable"
    fi
done

print_step "12. Test de génération QR Code"

if php artisan tinker --execute="
\$service = app('App\\Services\\QRCodeService');
try {
    \$qr = \$service->generate('test-data');
    echo 'QR_GENERATION_SUCCESS';
} catch(Exception \$e) {
    echo 'QR_GENERATION_FAILED: ' . \$e->getMessage();
}
" 2>/dev/null | grep -q "QR_GENERATION_SUCCESS"; then
    print_success "Génération de QR Code fonctionnelle"
else
    print_error "Échec de la génération de QR Code"
fi

echo ""
echo "==============================================="
echo "📊 Résumé de la validation"
echo "==============================================="
echo ""

if [ $ERRORS -eq 0 ]; then
    print_success "🎉 Toutes les validations sont passées avec succès !"
    print_success "✅ Nombre de tests réussis: $TESTS_PASSED"
    print_success "❌ Nombre de tests échoués: $TESTS_FAILED"
    echo ""
    echo -e "${GREEN}🚀 RestroSaaS Addons est entièrement fonctionnel !${NC}"
    echo ""
    echo "📋 Prochaines étapes recommandées :"
    echo "  • Démarrer le serveur de développement: php artisan serve"
    echo "  • Accéder à la documentation API: /api/documentation"
    echo "  • Exécuter les tests: php artisan test"
    echo "  • Seeder les données de démonstration: php artisan db:seed --class=AddonDemoSeeder"
    echo ""
    exit 0
else
    print_error "❌ Validation échouée avec $ERRORS erreur(s)"
    print_error "✅ Tests réussis: $TESTS_PASSED"
    print_error "❌ Tests échoués: $TESTS_FAILED"
    echo ""
    echo -e "${RED}🔧 Veuillez corriger les erreurs ci-dessus avant de continuer${NC}"
    echo ""
    exit 1
fi
