#!/bin/bash

# Script de validation sécurité pour RestroSaaS
# Vérifie la configuration de sécurité avant déploiement production

echo "🔐 VALIDATION SÉCURITÉ RESTOSAAS"
echo "================================"

# Couleurs pour output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction de validation
check_config() {
    local config_name="$1"
    local config_value="$2"
    local expected="$3"

    if [ "$config_value" = "$expected" ]; then
        echo -e "${GREEN}[✓]${NC} $config_name: $config_value"
        return 0
    else
        echo -e "${RED}[✗]${NC} $config_name: $config_value (attendu: $expected)"
        return 1
    fi
}

echo -e "${BLUE}[STEP]${NC} 1. Vérification Configuration Production"

# Lecture des variables .env
source .env 2>/dev/null || {
    echo -e "${RED}[ERROR]${NC} Fichier .env non trouvé"
    exit 1
}

ERRORS=0

# Vérification APP_ENV
check_config "APP_ENV" "$APP_ENV" "production" || ((ERRORS++))

# Vérification APP_DEBUG
check_config "APP_DEBUG" "$APP_DEBUG" "false" || ((ERRORS++))

# Vérification HTTPS
if [[ "$APP_URL" == https://* ]]; then
    echo -e "${GREEN}[✓]${NC} APP_URL: HTTPS configuré"
else
    echo -e "${RED}[✗]${NC} APP_URL: HTTP détecté (HTTPS requis en production)"
    ((ERRORS++))
fi

# Vérification FORCE_HTTPS
check_config "FORCE_HTTPS" "$FORCE_HTTPS" "true" || ((ERRORS++))

# Vérification SESSION_SECURE_COOKIE
check_config "SESSION_SECURE_COOKIE" "$SESSION_SECURE_COOKIE" "true" || ((ERRORS++))

echo -e "${BLUE}[STEP]${NC} 2. Vérification Middleware Sécurité"

# Vérification SecurityHeaders middleware
if grep -q "SecurityHeaders" app/Http/Kernel.php; then
    echo -e "${GREEN}[✓]${NC} Middleware SecurityHeaders configuré"
else
    echo -e "${RED}[✗]${NC} Middleware SecurityHeaders manquant"
    ((ERRORS++))
fi

echo -e "${BLUE}[STEP]${NC} 3. Vérification Permissions Fichiers"

# Vérification permissions storage
if [ -w "storage/" ]; then
    echo -e "${GREEN}[✓]${NC} Répertoire storage/ accessible en écriture"
else
    echo -e "${RED}[✗]${NC} Répertoire storage/ non accessible en écriture"
    ((ERRORS++))
fi

# Vérification permissions bootstrap/cache
if [ -w "bootstrap/cache/" ]; then
    echo -e "${GREEN}[✓]${NC} Répertoire bootstrap/cache/ accessible en écriture"
else
    echo -e "${RED}[✗]${NC} Répertoire bootstrap/cache/ non accessible en écriture"
    ((ERRORS++))
fi

echo -e "${BLUE}[STEP]${NC} 4. Vérification Configuration Base de Données"

# Test connexion base de données (si MySQL disponible)
if command -v mysql >/dev/null 2>&1; then
    if mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE; SELECT 1;" >/dev/null 2>&1; then
        echo -e "${GREEN}[✓]${NC} Connexion base de données fonctionnelle"
    else
        echo -e "${YELLOW}[!]${NC} Connexion base de données: Vérifiez les paramètres"
    fi
else
    echo -e "${YELLOW}[!]${NC} MySQL client non disponible pour test connexion"
fi

echo -e "${BLUE}[STEP]${NC} 5. Vérification Clés Secrètes"

# Vérification APP_KEY
if [ ${#APP_KEY} -gt 20 ]; then
    echo -e "${GREEN}[✓]${NC} APP_KEY configurée"
else
    echo -e "${RED}[✗]${NC} APP_KEY manquante ou invalide"
    ((ERRORS++))
fi

echo ""
echo "🔍 RÉSUMÉ VALIDATION SÉCURITÉ"
echo "=============================="

if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}🎉 Validation réussie! Configuration sécurisée pour production${NC}"
    echo -e "${GREEN}✅ Nombre de vérifications réussies: $(grep -c '\[✓\]' <<< "$(cat)")"
    echo -e "${GREEN}❌ Nombre d'erreurs: 0${NC}"
    exit 0
else
    echo -e "${RED}⚠️  Validation échouée! $ERRORS erreur(s) de sécurité détectée(s)${NC}"
    echo -e "${RED}❌ Corrigez les erreurs avant déploiement production${NC}"
    exit 1
fi
