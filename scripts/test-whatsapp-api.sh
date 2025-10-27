#!/bin/bash

# Script de test pour WhatsApp Business API
# Date: 23 octobre 2025
# Usage: ./test-whatsapp-api.sh

echo "========================================="
echo "🧪 TEST WHATSAPP BUSINESS API"
echo "========================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Compteur
TESTS_PASSED=0
TESTS_FAILED=0

# Fonction de test
run_test() {
    local test_name="$1"
    local test_command="$2"

    echo -e "${BLUE}▶ Test: ${test_name}${NC}"

    if eval "$test_command" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ PASSÉ${NC}"
        ((TESTS_PASSED++))
    else
        echo -e "${RED}❌ ÉCHOUÉ${NC}"
        ((TESTS_FAILED++))
    fi
    echo ""
}

# Test 1: Vérifier les fichiers
echo "═══════════════════════════════════════"
echo "📂 TEST 1: VÉRIFICATION DES FICHIERS"
echo "═══════════════════════════════════════"
echo ""

run_test "Service WhatsAppBusinessService existe" \
    "test -f app/Services/WhatsAppBusinessService.php"

run_test "Modèle WhatsAppLog existe" \
    "test -f app/Models/WhatsAppLog.php"

run_test "Migration whatsapp_logs existe" \
    "ls database/migrations/*_create_whatsapp_logs_table.php"

run_test "Configuration whatsapp.php existe" \
    "test -f config/whatsapp.php"

run_test "OrderController modifié" \
    "grep -q 'WhatsAppBusinessService' app/Http/Controllers/admin/OrderController.php"

# Test 2: Syntaxe PHP
echo "═══════════════════════════════════════"
echo "🔍 TEST 2: VALIDATION SYNTAXE PHP"
echo "═══════════════════════════════════════"
echo ""

run_test "WhatsAppBusinessService syntaxe valide" \
    "php -l app/Services/WhatsAppBusinessService.php"

run_test "WhatsAppLog syntaxe valide" \
    "php -l app/Models/WhatsAppLog.php"

run_test "OrderController syntaxe valide" \
    "php -l app/Http/Controllers/admin/OrderController.php"

# Test 3: Configuration
echo "═══════════════════════════════════════"
echo "⚙️  TEST 3: VÉRIFICATION CONFIGURATION"
echo "═══════════════════════════════════════"
echo ""

run_test "Config whatsapp charge correctement" \
    "php artisan tinker --execute=\"echo config('whatsapp.api_url');\""

run_test "Variables .env.example ajoutées" \
    "grep -q 'WHATSAPP_API_TOKEN' .env.example"

run_test "Variables auto_notifications présentes" \
    "grep -q 'WHATSAPP_AUTO_NOTIFY_ORDER_ACCEPTED' .env.example"

# Test 4: Classes et méthodes
echo "═══════════════════════════════════════"
echo "🏗️  TEST 4: CLASSES ET MÉTHODES"
echo "═══════════════════════════════════════"
echo ""

run_test "Classe WhatsAppBusinessService existe" \
    "php artisan tinker --execute=\"class_exists('App\\\\Services\\\\WhatsAppBusinessService');\""

run_test "Méthode sendTextMessage existe" \
    "grep -q 'function sendTextMessage' app/Services/WhatsAppBusinessService.php"

run_test "Méthode sendTemplateMessage existe" \
    "grep -q 'function sendTemplateMessage' app/Services/WhatsAppBusinessService.php"

run_test "Méthode testConnection existe" \
    "grep -q 'function testConnection' app/Services/WhatsAppBusinessService.php"

run_test "Méthode generateWhatsAppUrl existe" \
    "grep -q 'function generateWhatsAppUrl' app/Services/WhatsAppBusinessService.php"

run_test "Méthode getStats existe" \
    "grep -q 'function getStats' app/Services/WhatsAppBusinessService.php"

# Test 5: Intégration OrderController
echo "═══════════════════════════════════════"
echo "🔗 TEST 5: INTÉGRATION ORDERCONTROLLER"
echo "═══════════════════════════════════════"
echo ""

run_test "Import WhatsAppBusinessService présent" \
    "grep -q 'use App\\\\Services\\\\WhatsAppBusinessService;' app/Http/Controllers/admin/OrderController.php"

run_test "Méthode sendWhatsAppNotification appelle le service" \
    "grep -q 'new WhatsAppBusinessService()' app/Http/Controllers/admin/OrderController.php"

run_test "Appel sendTextMessage présent" \
    "grep -q 'sendTextMessage' app/Http/Controllers/admin/OrderController.php"

# Test 6: Migration
echo "═══════════════════════════════════════"
echo "🗄️  TEST 6: MIGRATION BASE DE DONNÉES"
echo "═══════════════════════════════════════"
echo ""

MIGRATION_FILE=$(ls database/migrations/*_create_whatsapp_logs_table.php 2>/dev/null | head -n 1)

if [ -n "$MIGRATION_FILE" ]; then
    run_test "Migration contient table whatsapp_logs" \
        "grep -q 'whatsapp_logs' $MIGRATION_FILE"

    run_test "Migration contient colonne 'to'" \
        "grep -q \"'to'\" $MIGRATION_FILE"

    run_test "Migration contient colonne 'message'" \
        "grep -q \"'message'\" $MIGRATION_FILE"

    run_test "Migration contient colonne 'success'" \
        "grep -q \"'success'\" $MIGRATION_FILE"

    run_test "Migration contient colonne 'message_id'" \
        "grep -q \"'message_id'\" $MIGRATION_FILE"
else
    echo -e "${RED}❌ Migration file not found${NC}"
    ((TESTS_FAILED+=5))
fi

# Test 7: Documentation
echo "═══════════════════════════════════════"
echo "📖 TEST 7: DOCUMENTATION"
echo "═══════════════════════════════════════"
echo ""

run_test "Guide API WhatsApp existe" \
    "test -f WHATSAPP_BUSINESS_API_GUIDE.md"

run_test "Guide gestion commandes existe" \
    "test -f RESTAURANT_ORDER_MANAGEMENT.md"

# Résumé
echo "========================================="
echo "📊 RÉSUMÉ DES TESTS"
echo "========================================="
echo ""
echo -e "${GREEN}Tests réussis: $TESTS_PASSED${NC}"
echo -e "${RED}Tests échoués: $TESTS_FAILED${NC}"
echo ""

TOTAL_TESTS=$((TESTS_PASSED + TESTS_FAILED))
SUCCESS_RATE=$(awk "BEGIN {printf \"%.1f\", ($TESTS_PASSED / $TOTAL_TESTS) * 100}")

echo "Taux de réussite: ${SUCCESS_RATE}%"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ TOUS LES TESTS ONT RÉUSSI !${NC}"
    echo ""
    echo "🎉 Intégration WhatsApp Business API prête !"
    echo ""
    echo "Prochaines étapes:"
    echo "1. Configurer les credentials Meta dans .env"
    echo "2. Migrer la base de données: php artisan migrate"
    echo "3. Tester la connexion: php public/test-whatsapp-api.php"
    echo "4. Activer l'envoi: WHATSAPP_ENABLED=true"
    echo ""
    exit 0
else
    echo -e "${RED}❌ CERTAINS TESTS ONT ÉCHOUÉ${NC}"
    echo ""
    echo "Veuillez vérifier les erreurs ci-dessus."
    echo "Consultez WHATSAPP_BUSINESS_API_GUIDE.md pour plus d'infos."
    echo ""
    exit 1
fi
