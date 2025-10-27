#!/bin/bash

# 📚 Script de Validation Documentation RestroSaaS
# Vérifie que tous les guides sont présents et accessibles

echo "📚 VALIDATION DOCUMENTATION RESTOSAAS"
echo "===================================="
echo ""

# Couleurs pour affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Compteurs
TOTAL_FILES=0
VALID_FILES=0
MISSING_FILES=0

# Fonction de vérification fichier
check_file() {
    local file=$1
    local description=$2

    TOTAL_FILES=$((TOTAL_FILES + 1))

    if [ -f "$file" ]; then
        local size=$(wc -c < "$file")
        if [ $size -gt 1000 ]; then
            echo -e "  ✅ ${GREEN}$description${NC} (${size} bytes)"
            VALID_FILES=$((VALID_FILES + 1))
        else
            echo -e "  ⚠️  ${YELLOW}$description (fichier trop petit: ${size} bytes)${NC}"
        fi
    else
        echo -e "  ❌ ${RED}$description (MANQUANT)${NC}"
        MISSING_FILES=$((MISSING_FILES + 1))
    fi
}

# Dossier documentation
DOC_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/Documentation"

echo -e "${BLUE}📖 Documentation Utilisateurs${NC}"
check_file "$DOC_DIR/README.md" "Index Principal Documentation"
check_file "$DOC_DIR/GUIDE_CLIENT.md" "Guide Client Complet"
check_file "$DOC_DIR/GUIDE_RESTAURANT.md" "Guide Restaurant"
check_file "$DOC_DIR/GUIDE_TECHNIQUE.md" "Guide Technique Installation"

echo ""
echo -e "${BLUE}🎓 Formation et Administration${NC}"
check_file "$DOC_DIR/GUIDE_FORMATION_ADMIN.md" "Guide Formation Équipe Admin"
check_file "$DOC_DIR/GUIDE_MISE_EN_PRODUCTION.md" "Guide Mise en Production"
check_file "$DOC_DIR/FAQ.md" "FAQ Questions Fréquentes"

echo ""
echo -e "${BLUE}📋 Documentation Technique Racine${NC}"
check_file "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/SECURITY_GUIDE.md" "Guide Sécurité Production"
check_file "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/INSTALLATION.md" "Installation Rapide"
check_file "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/README.md" "README Principal Projet"

echo ""
echo -e "${BLUE}🔧 Guides Spécialisés${NC}"
check_file "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/CINETPAY_INTEGRATION.md" "Intégration CinetPay"
check_file "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/PWA_README.md" "Configuration PWA"
check_file "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas/FIREBASE_SETUP.md" "Setup Firebase"

echo ""
echo "======================================"
echo -e "${BLUE}📊 RÉSUMÉ VALIDATION${NC}"
echo "======================================"
echo -e "Total fichiers vérifiés: ${TOTAL_FILES}"
echo -e "${GREEN}✅ Fichiers valides: ${VALID_FILES}${NC}"

if [ $MISSING_FILES -gt 0 ]; then
    echo -e "${RED}❌ Fichiers manquants: ${MISSING_FILES}${NC}"
    echo ""
    echo -e "${RED}⚠️  ATTENTION: Documentation incomplète !${NC}"
    exit 1
else
    echo -e "${RED}❌ Fichiers manquants: 0${NC}"
    echo ""
    echo -e "${GREEN}🎉 DOCUMENTATION COMPLÈTE ET VALIDÉE !${NC}"
    echo ""

    # Statistiques détaillées
    echo -e "${BLUE}📊 Statistiques Documentation:${NC}"

    # Taille totale
    TOTAL_SIZE=$(find "$DOC_DIR" -name "*.md" -exec wc -c {} + 2>/dev/null | tail -n 1 | awk '{print $1}')
    if [ ! -z "$TOTAL_SIZE" ]; then
        TOTAL_MB=$(echo "scale=2; $TOTAL_SIZE / 1048576" | bc 2>/dev/null || echo "?")
        echo "  📁 Taille totale documentation: ${TOTAL_MB} MB"
    fi

    # Nombre de lignes
    TOTAL_LINES=$(find "$DOC_DIR" -name "*.md" -exec wc -l {} + 2>/dev/null | tail -n 1 | awk '{print $1}')
    if [ ! -z "$TOTAL_LINES" ]; then
        echo "  📄 Lignes de documentation: ${TOTAL_LINES}"
    fi

    echo ""
    echo -e "${GREEN}✅ PRÊT POUR UTILISATION EN PRODUCTION${NC}"
fi

echo ""
echo "🔗 Accès documentation: /Documentation/README.md"
echo "📞 Support: https://restro-saas.com/support"
echo ""
