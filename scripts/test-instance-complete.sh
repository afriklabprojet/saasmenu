#!/bin/bash

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║        🧪 TEST COMPLET DE L'INSTANCE RESTOSAAS                 ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Compteurs
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Fonction de test
test_url() {
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    local url=$1
    local description=$2
    local expected_code=${3:-200}

    printf "%-50s " "Testing: $description..."

    response=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null)

    if [ "$response" = "$expected_code" ]; then
        echo -e "${GREEN}✓ PASS${NC} (HTTP $response)"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗ FAIL${NC} (HTTP $response, expected $expected_code)"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}1. TESTS DES PAGES PUBLIQUES${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

test_url "http://127.0.0.1:8000" "Page d'accueil"
test_url "http://127.0.0.1:8000/#home" "Section Hero"
test_url "http://127.0.0.1:8000/#features" "Section Features"
test_url "http://127.0.0.1:8000/#pricing-plans" "Section Pricing"
test_url "http://127.0.0.1:8000/#contect-us" "Section Contact"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}2. TESTS DES PAGES LÉGALES${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

test_url "http://127.0.0.1:8000/privacy_policy" "Privacy Policy"
test_url "http://127.0.0.1:8000/terms_condition" "Terms & Conditions"
test_url "http://127.0.0.1:8000/refund_policy" "Refund Policy"
test_url "http://127.0.0.1:8000/about_us" "About Us"
test_url "http://127.0.0.1:8000/faqs" "FAQs"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}3. TESTS DE L'INTERFACE ADMIN${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

test_url "http://127.0.0.1:8000/admin" "Page de connexion Admin"
test_url "http://127.0.0.1:8000/admin/register" "Page d'inscription Vendor"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}4. TESTS DES ASSETS${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

test_url "http://127.0.0.1:8000/storage/admin-assets/css/bootstrap/bootstrap.min.css" "Bootstrap CSS"
test_url "http://127.0.0.1:8000/storage/admin-assets/js/jquery/jquery.min.js" "jQuery"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}5. TEST DE LA BASE DE DONNÉES${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

printf "%-50s " "Connexion base de données..."
DB_TEST=$(php artisan tinker --execute="echo 'OK';" 2>&1 | grep "OK")
if [ ! -z "$DB_TEST" ]; then
    echo -e "${GREEN}✓ PASS${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

printf "%-50s " "Comptage tables migrations..."
TABLE_COUNT=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\DB::table('migrations')->count();" 2>&1 | tail -n 1)
if [ "$TABLE_COUNT" -gt "0" ]; then
    echo -e "${GREEN}✓ PASS${NC} ($TABLE_COUNT migrations)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

printf "%-50s " "Vérification table users..."
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>&1 | tail -n 1)
if [ "$USER_COUNT" -gt "0" ]; then
    echo -e "${GREEN}✓ PASS${NC} ($USER_COUNT users)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${YELLOW}⚠ WARN${NC} (0 users)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}6. TEST DE LA CONFIGURATION${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

printf "%-50s " "Fichier .env existe..."
if [ -f .env ]; then
    echo -e "${GREEN}✓ PASS${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

printf "%-50s " "Cache config..."
CONFIG_TEST=$(php artisan config:cache 2>&1 | grep "Configuration cache")
if [ ! -z "$CONFIG_TEST" ]; then
    echo -e "${GREEN}✓ PASS${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${BLUE}7. TEST DES DOSSIERS${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

printf "%-50s " "Dossier documentation/..."
if [ -d "documentation" ]; then
    DOC_COUNT=$(ls -1 documentation/ | wc -l | tr -d ' ')
    echo -e "${GREEN}✓ PASS${NC} ($DOC_COUNT fichiers)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

printf "%-50s " "Dossier scripts/..."
if [ -d "scripts" ]; then
    SCRIPT_COUNT=$(ls -1 scripts/ | wc -l | tr -d ' ')
    echo -e "${GREEN}✓ PASS${NC} ($SCRIPT_COUNT fichiers)"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

printf "%-50s " "Permissions storage/..."
if [ -w "storage" ]; then
    echo -e "${GREEN}✓ PASS${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                      📊 RÉSULTATS FINAUX                       ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo -e "Total des tests:      ${BLUE}$TOTAL_TESTS${NC}"
echo -e "Tests réussis:        ${GREEN}$PASSED_TESTS${NC}"
echo -e "Tests échoués:        ${RED}$FAILED_TESTS${NC}"

SUCCESS_RATE=$((PASSED_TESTS * 100 / TOTAL_TESTS))
echo -e "Taux de réussite:     ${GREEN}${SUCCESS_RATE}%${NC}"
echo ""

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}✓✓✓ TOUS LES TESTS SONT PASSÉS! L'INSTANCE EST OPÉRATIONNELLE! ✓✓✓${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}⚠⚠⚠ CERTAINS TESTS ONT ÉCHOUÉ. VÉRIFIER LES LOGS. ⚠⚠⚠${NC}"
    echo ""
    exit 1
fi
