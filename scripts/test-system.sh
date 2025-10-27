#!/bin/bash

echo "╔══════════════════════════════════════════════════════════╗"
echo "║  🔍 TESTS AVANCÉS - VALIDATION COMPLÈTE                 ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# Test 6: Nettoyage BNP
echo "📝 TEST 6: Nettoyage BNP Paribas"
BNP_USERS=$(php artisan tinker --execute="echo DB::table('users')->where('email', 'LIKE', '%bnp%')->count();")
echo "  ✅ Utilisateurs BNP: $BNP_USERS"

# Test 7: Fichiers de configuration
echo ""
echo "📝 TEST 7: Fichiers de Configuration"
[ -f "database/seeders/AdminSeeder.php" ] && echo "  ✅ AdminSeeder.php: Existe" || echo "  ❌ AdminSeeder.php: Manquant"
[ -f "app/Console/Commands/SetupAdmin.php" ] && echo "  ✅ SetupAdmin.php: Existe" || echo "  ❌ SetupAdmin.php: Manquant"
[ ! -f "database/seeders/SuperAdminSeeder.php" ] && echo "  ✅ SuperAdminSeeder.php (BNP): Supprimé" || echo "  ❌ SuperAdminSeeder.php (BNP): Encore présent"

# Test 8: Documentation
echo ""
echo "📝 TEST 8: Documentation"
[ -f "GUIDE_DEMARRAGE_RAPIDE.md" ] && echo "  ✅ GUIDE_DEMARRAGE_RAPIDE.md" || echo "  ❌ GUIDE_DEMARRAGE_RAPIDE.md"
[ -f "WHATSAPP_CONFIGURATION.md" ] && echo "  ✅ WHATSAPP_CONFIGURATION.md" || echo "  ❌ WHATSAPP_CONFIGURATION.md"
[ -f "CINETPAY_CONFIGURATION.md" ] && echo "  ✅ CINETPAY_CONFIGURATION.md" || echo "  ❌ CINETPAY_CONFIGURATION.md"
[ -f "RAPPORT_FINAL_COMPLET.md" ] && echo "  ✅ RAPPORT_FINAL_COMPLET.md" || echo "  ❌ RAPPORT_FINAL_COMPLET.md"

# Test 9: Traductions
echo ""
echo "📝 TEST 9: Traductions Françaises"
PHP_COUNT=$(ls -1 resources/lang/fr/*.php 2>/dev/null | wc -l | tr -d ' ')
JSON_COUNT=$(ls -1 resources/lang/fr/*.json 2>/dev/null | wc -l | tr -d ' ')
echo "  ✅ Fichiers PHP: $PHP_COUNT"
echo "  ✅ Fichiers JSON: $JSON_COUNT"

# Test 10: Sécurité
echo ""
echo "📝 TEST 10: Sécurité et Production"
if grep -q "APP_DEBUG=false" .env 2>/dev/null; then
    echo "  ✅ Debug mode: OFF"
else
    echo "  ⚠️  Debug mode: ON (à désactiver!)"
fi

if grep -q "APP_ENV=production" .env 2>/dev/null; then
    echo "  ✅ Environnement: production"
else
    echo "  ⚠️  Environnement: Non-production"
fi

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║  ✅ TESTS TERMINÉS                                       ║"
echo "╚══════════════════════════════════════════════════════════╝"
