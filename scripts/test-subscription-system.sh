#!/bin/bash

# Script de test manuel pour le système d'abonnement
# RestroSaaS - Tests Fonctionnels

echo "╔═══════════════════════════════════════════════════════════════════╗"
echo "║                                                                   ║"
echo "║          🧪 SCRIPT DE TESTS FONCTIONNELS - RESTRO SAAS            ║"
echo "║                                                                   ║"
echo "╚═══════════════════════════════════════════════════════════════════╝"
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Compteurs
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_TOTAL=0

# Fonction pour afficher un test
test_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}🧪 TEST $1: $2${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# Fonction pour marquer un test réussi
test_pass() {
    echo -e "${GREEN}✅ $1${NC}"
    ((TESTS_PASSED++))
    ((TESTS_TOTAL++))
}

# Fonction pour marquer un test échoué
test_fail() {
    echo -e "${RED}❌ $1${NC}"
    ((TESTS_FAILED++))
    ((TESTS_TOTAL++))
}

# Fonction pour afficher un warning
test_warn() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erreur: Ce script doit être exécuté depuis le répertoire racine de Laravel${NC}"
    exit 1
fi

echo "📁 Répertoire de travail: $(pwd)"
echo ""

# ============================================================================
# TEST 1: Vérification de l'environnement
# ============================================================================
test_header "1" "Vérification de l'environnement"

# Vérifier PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    test_pass "PHP installé: $PHP_VERSION"
else
    test_fail "PHP n'est pas installé"
fi

# Vérifier Laravel
if php artisan --version &> /dev/null; then
    LARAVEL_VERSION=$(php artisan --version)
    test_pass "Laravel: $LARAVEL_VERSION"
else
    test_fail "Erreur Laravel"
fi

# Vérifier la connexion DB
if php artisan db:show &> /dev/null; then
    test_pass "Connexion base de données OK"
else
    test_warn "Impossible de vérifier la connexion DB"
fi

# ============================================================================
# TEST 2: Vérification des migrations
# ============================================================================
test_header "2" "Vérification des migrations"

# Vérifier que la migration des limites existe
if php artisan migrate:status | grep -q "add_limits_to_pricing_plans"; then
    test_pass "Migration 'add_limits_to_pricing_plans' trouvée"
else
    test_fail "Migration 'add_limits_to_pricing_plans' manquante"
fi

# Vérifier l'état des migrations
PENDING=$(php artisan migrate:status | grep -c "Pending")
if [ "$PENDING" -eq 0 ]; then
    test_pass "Toutes les migrations sont exécutées"
else
    test_warn "$PENDING migration(s) en attente"
fi

# ============================================================================
# TEST 3: Vérification de la structure de la base de données
# ============================================================================
test_header "3" "Structure de la base de données"

echo "Vérification des colonnes dans pricing_plans..."

# Test via tinker
TINKER_SCRIPT="
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

\$columns = Schema::getColumnListing('pricing_plans');
\$required = ['products_limit', 'categories_limit', 'staff_limit', 'whatsapp_integration', 'analytics'];

\$missing = [];
foreach (\$required as \$col) {
    if (!in_array(\$col, \$columns)) {
        \$missing[] = \$col;
    }
}

if (empty(\$missing)) {
    echo 'ALL_OK';
} else {
    echo 'MISSING:' . implode(',', \$missing);
}
"

RESULT=$(echo "$TINKER_SCRIPT" | php artisan tinker 2>/dev/null | grep -E "ALL_OK|MISSING" | tail -1)

if [[ "$RESULT" == *"ALL_OK"* ]]; then
    test_pass "Toutes les colonnes requises sont présentes"
else
    test_fail "Colonnes manquantes: ${RESULT#MISSING:}"
fi

# ============================================================================
# TEST 4: Vérification des contrôleurs
# ============================================================================
test_header "4" "Vérification des contrôleurs"

# Vérifier PlanPricingController
if grep -q "products_limit" app/Http/Controllers/Admin/PlanPricingController.php; then
    test_pass "PlanPricingController contient 'products_limit'"
else
    test_fail "PlanPricingController ne contient pas 'products_limit'"
fi

if grep -q "categories_limit" app/Http/Controllers/Admin/PlanPricingController.php; then
    test_pass "PlanPricingController contient 'categories_limit'"
else
    test_fail "PlanPricingController ne contient pas 'categories_limit'"
fi

# Vérifier ProductController
if grep -q "getPlanInfo" app/Http/Controllers/Admin/ProductController.php; then
    test_pass "ProductController utilise getPlanInfo()"
else
    test_fail "ProductController n'utilise pas getPlanInfo()"
fi

# Vérifier CategoryController
if grep -q "getPlanInfo" app/Http/Controllers/Admin/CategoryController.php; then
    test_pass "CategoryController utilise getPlanInfo()"
else
    test_fail "CategoryController n'utilise pas getPlanInfo()"
fi

# ============================================================================
# TEST 5: Vérification du helper
# ============================================================================
test_header "5" "Vérification du helper getPlanInfo()"

if grep -q "function getPlanInfo" app/Helpers/helper.php; then
    test_pass "Fonction getPlanInfo() existe"

    # Vérifier qu'elle retourne un tableau
    if grep -A 20 "function getPlanInfo" app/Helpers/helper.php | grep -q "return \["; then
        test_pass "getPlanInfo() retourne un tableau"
    else
        test_warn "getPlanInfo() pourrait ne pas retourner un tableau"
    fi
else
    test_fail "Fonction getPlanInfo() manquante"
fi

# ============================================================================
# TEST 6: Vérification des templates
# ============================================================================
test_header "6" "Vérification des templates Blade"

# product.blade.php
if [ -f "resources/views/admin/product/product.blade.php" ]; then
    if grep -q "getPlanInfo" resources/views/admin/product/product.blade.php; then
        test_pass "product.blade.php contient l'indicateur de limite"
    else
        test_fail "product.blade.php ne contient pas l'indicateur"
    fi
else
    test_fail "product.blade.php n'existe pas"
fi

# category.blade.php
if [ -f "resources/views/admin/category/category.blade.php" ]; then
    if grep -q "getPlanInfo" resources/views/admin/category/category.blade.php; then
        test_pass "category.blade.php contient l'indicateur de limite"
    else
        test_fail "category.blade.php ne contient pas l'indicateur"
    fi
else
    test_fail "category.blade.php n'existe pas"
fi

# add_plan.blade.php
if [ -f "resources/views/admin/plan/add_plan.blade.php" ]; then
    if grep -q "products_limit" resources/views/admin/plan/add_plan.blade.php; then
        test_pass "add_plan.blade.php contient les nouveaux champs"
    else
        test_fail "add_plan.blade.php ne contient pas les nouveaux champs"
    fi
else
    test_fail "add_plan.blade.php n'existe pas"
fi

# ============================================================================
# TEST 7: Vérification des traductions
# ============================================================================
test_header "7" "Vérification des traductions françaises"

if [ -f "resources/lang/fr/labels.php" ]; then
    if grep -q "product_limit_reached" resources/lang/fr/labels.php; then
        test_pass "Traduction 'product_limit_reached' existe"
    else
        test_fail "Traduction 'product_limit_reached' manquante"
    fi

    if grep -q "category_limit_reached" resources/lang/fr/labels.php; then
        test_pass "Traduction 'category_limit_reached' existe"
    else
        test_fail "Traduction 'category_limit_reached' manquante"
    fi

    if grep -q "upgrade_to_add_more" resources/lang/fr/labels.php; then
        test_pass "Traduction 'upgrade_to_add_more' existe"
    else
        test_fail "Traduction 'upgrade_to_add_more' manquante"
    fi
else
    test_fail "Fichier de traduction FR n'existe pas"
fi

# ============================================================================
# TEST 8: Vérification des routes
# ============================================================================
test_header "8" "Vérification des routes"

if php artisan route:list | grep -q "products/add"; then
    test_pass "Route 'products/add' existe"
else
    test_fail "Route 'products/add' manquante"
fi

if php artisan route:list | grep -q "categories/add"; then
    test_pass "Route 'categories/add' existe"
else
    test_fail "Route 'categories/add' manquante"
fi

if php artisan route:list | grep -q "plan/save_plan"; then
    test_pass "Route 'plan/save_plan' existe"
else
    test_fail "Route 'plan/save_plan' manquante"
fi

# ============================================================================
# TEST 9: Vérification des caches
# ============================================================================
test_header "9" "État des caches"

echo "Vérification des fichiers de cache..."

if [ -d "bootstrap/cache" ] && [ "$(ls -A bootstrap/cache)" ]; then
    test_warn "Des fichiers de cache existent dans bootstrap/cache"
    echo "   💡 Recommandation: Exécutez 'php artisan optimize:clear'"
else
    test_pass "Pas de cache dans bootstrap/cache"
fi

if [ -d "storage/framework/cache" ] && [ "$(ls -A storage/framework/cache/data 2>/dev/null)" ]; then
    test_warn "Des fichiers de cache existent dans storage/framework/cache"
else
    test_pass "Pas de cache dans storage/framework/cache"
fi

# ============================================================================
# TEST 10: Test des données
# ============================================================================
test_header "10" "Vérification des données des plans"

PLAN_CHECK="
use App\Models\PricingPlan;

\$free = PricingPlan::where('price', 0)->first();
if (\$free && \$free->products_limit == 5 && \$free->categories_limit == 1) {
    echo 'FREE_OK';
} else {
    echo 'FREE_ERROR';
}

\$enterprise = PricingPlan::where('products_limit', -1)->first();
if (\$enterprise) {
    echo '|ENTERPRISE_OK';
} else {
    echo '|ENTERPRISE_ERROR';
}
"

RESULT=$(echo "$PLAN_CHECK" | php artisan tinker 2>/dev/null | grep -E "FREE_OK|FREE_ERROR|ENTERPRISE" | tail -1)

if [[ "$RESULT" == *"FREE_OK"* ]]; then
    test_pass "Plan Gratuit configuré correctement (5 produits, 1 catégorie)"
else
    test_fail "Plan Gratuit mal configuré"
fi

if [[ "$RESULT" == *"ENTERPRISE_OK"* ]]; then
    test_pass "Plan Enterprise illimité existe"
else
    test_warn "Aucun plan illimité trouvé"
fi

# ============================================================================
# RÉSUMÉ FINAL
# ============================================================================
echo ""
echo "╔═══════════════════════════════════════════════════════════════════╗"
echo "║                                                                   ║"
echo "║                     📊 RÉSUMÉ DES TESTS                            ║"
echo "║                                                                   ║"
echo "╚═══════════════════════════════════════════════════════════════════╝"
echo ""

echo -e "${BLUE}Tests totaux:${NC}     $TESTS_TOTAL"
echo -e "${GREEN}Tests réussis:${NC}    $TESTS_PASSED"
echo -e "${RED}Tests échoués:${NC}    $TESTS_FAILED"

if [ $TESTS_FAILED -eq 0 ]; then
    echo ""
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                                                                   ║${NC}"
    echo -e "${GREEN}║          ✅ TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS                ║${NC}"
    echo -e "${GREEN}║                                                                   ║${NC}"
    echo -e "${GREEN}║     🚀 Le système est prêt pour les tests manuels                 ║${NC}"
    echo -e "${GREEN}║                                                                   ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════════════╝${NC}"
    exit 0
else
    SUCCESS_RATE=$((TESTS_PASSED * 100 / TESTS_TOTAL))
    echo ""
    echo -e "${YELLOW}╔═══════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${YELLOW}║                                                                   ║${NC}"
    echo -e "${YELLOW}║        ⚠️  CERTAINS TESTS ONT ÉCHOUÉ ($SUCCESS_RATE% réussis)              ║${NC}"
    echo -e "${YELLOW}║                                                                   ║${NC}"
    echo -e "${YELLOW}║     Consultez les détails ci-dessus pour corriger les erreurs    ║${NC}"
    echo -e "${YELLOW}║                                                                   ║${NC}"
    echo -e "${YELLOW}╚═══════════════════════════════════════════════════════════════════╝${NC}"
    exit 1
fi
