#!/bin/bash

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     📦 CRÉATION ARCHIVE BETA - VERSION PROPRE                  ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Nom de l'archive
ARCHIVE_NAME="restro-saas-beta-$(date +%Y%m%d-%H%M%S).zip"
ARCHIVE_PATH="../$ARCHIVE_NAME"

echo -e "${BLUE}📋 Préparation de l'archive...${NC}"
echo ""

# Créer l'archive en excluant les fichiers inutiles
echo -e "${YELLOW}🗂️  Fichiers exclus:${NC}"
echo "   • node_modules/"
echo "   • vendor/"
echo "   • storage/logs/"
echo "   • storage/framework/cache/"
echo "   • storage/framework/sessions/"
echo "   • storage/framework/views/"
echo "   • .git/"
echo "   • .env (sécurité)"
echo "   • *.log"
echo "   • .DS_Store"
echo "   • Thumbs.db"
echo "   • sw.js.disabled"
echo ""

echo -e "${BLUE}📦 Création de l'archive...${NC}"

zip -r "$ARCHIVE_PATH" . \
    -x "node_modules/*" \
    -x "vendor/*" \
    -x "storage/logs/*" \
    -x "storage/framework/cache/*" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/views/*" \
    -x ".git/*" \
    -x ".env" \
    -x "*.log" \
    -x ".DS_Store" \
    -x "Thumbs.db" \
    -x "public/sw.js.disabled" \
    -x ".gitignore" \
    -x ".gitattributes" \
    -q

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Archive créée avec succès!${NC}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "📦 Détails de l'archive:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Nom:         $ARCHIVE_NAME"
    echo "Emplacement: $(cd .. && pwd)/$ARCHIVE_NAME"
    
    # Taille de l'archive
    if [[ "$OSTYPE" == "darwin"* ]]; then
        SIZE=$(du -h "$ARCHIVE_PATH" | cut -f1)
    else
        SIZE=$(du -h "$ARCHIVE_PATH" | awk '{print $1}')
    fi
    echo "Taille:      $SIZE"
    
    # Nombre de fichiers
    FILE_COUNT=$(unzip -l "$ARCHIVE_PATH" | tail -1 | awk '{print $2}')
    echo "Fichiers:    $FILE_COUNT fichiers"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo -e "${GREEN}✅ Prêt pour distribution!${NC}"
else
    echo ""
    echo -e "${RED}❌ Erreur lors de la création de l'archive${NC}"
    exit 1
fi
