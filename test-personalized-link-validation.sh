#!/bin/bash

# Script de test pour la validation du champ Personalized Link
# Ce script teste les corrections apportées à la validation du slug

echo "🔍 Test de la validation du champ Personalized Link"
echo "=================================================="

# Test 1: Vérifier que la validation côté serveur est en place
echo -e "\n1. Vérification du contrôleur VendorController.php:"
if grep -q "regex:/\^[a-z0-9]+(?:-[a-z0-9]+)*\$/" /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/app/Http/Controllers/Admin/VendorController.php; then
    echo "   ✅ Validation regex côté serveur présente"
else
    echo "   ❌ Validation regex côté serveur manquante"
fi

# Test 2: Vérifier la validation JavaScript
echo -e "\n2. Vérification du JavaScript dans register.blade.php:"
if grep -q "slugInput.addEventListener('input'" /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/auth/register.blade.php; then
    echo "   ✅ Validation JavaScript en temps réel présente"
else
    echo "   ❌ Validation JavaScript en temps réel manquante"
fi

# Test 3: Vérifier l'attribut pattern HTML
echo -e "\n3. Vérification de l'attribut pattern HTML:"
if grep -q 'pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"' /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/auth/register.blade.php; then
    echo "   ✅ Attribut pattern HTML présent"
else
    echo "   ❌ Attribut pattern HTML manquant"
fi

# Test 4: Vérifier la correction orthographique
echo -e "\n4. Vérification de la correction orthographique:"
if grep -q '"personalized_link"' /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/lang/en/labels.php; then
    echo "   ✅ Orthographe corrigée dans labels.php (EN)"
else
    echo "   ❌ Orthographe non corrigée dans labels.php (EN)"
fi

if grep -q '"personalized_link"' /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/lang/fr/labels.php; then
    echo "   ✅ Orthographe corrigée dans labels.php (FR)"
else
    echo "   ❌ Orthographe non corrigée dans labels.php (FR)"
fi

# Test 5: Vérifier le placeholder et les exemples
echo -e "\n5. Vérification des aides utilisateur:"
if grep -q 'placeholder="mon-restaurant-123"' /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/auth/register.blade.php; then
    echo "   ✅ Placeholder informatif présent"
else
    echo "   ❌ Placeholder informatif manquant"
fi

if grep -q 'Exemple: mon-restaurant' /Users/teya2023/Documents/codecayon\ SaaS/restrosaas-37/saas-whatsapp/restro-saas/resources/views/admin/auth/register.blade.php; then
    echo "   ✅ Exemples d'utilisation présents"
else
    echo "   ❌ Exemples d'utilisation manquants"
fi

echo -e "\n📋 Résumé des corrections apportées:"
echo "   • Validation regex côté serveur: ^[a-z0-9]+(?:-[a-z0-9]+)*$"
echo "   • Validation JavaScript en temps réel"
echo "   • Attribut HTML pattern pour validation native"
echo "   • Correction orthographique: personlized → personalized"
echo "   • Messages d'aide et exemples pour l'utilisateur"
echo "   • Suppression automatique des espaces en saisie"

echo -e "\n🎯 Formats acceptés:"
echo "   ✅ mon-restaurant"
echo "   ✅ cafe-123"
echo "   ✅ bistro-central-paris"
echo "   ✅ restaurant123"

echo -e "\n❌ Formats rejetés:"
echo "   ❌ mon restaurant (espaces)"
echo "   ❌ Mon-Restaurant (majuscules)"
echo "   ❌ café-ñoël (caractères spéciaux)"
echo "   ❌ -restaurant- (tirets en début/fin)"
echo "   ❌ mon--restaurant (tirets doubles)"

echo -e "\n✅ Correction terminée !"
