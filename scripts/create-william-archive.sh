#!/bin/bash

# 🎯 SCRIPT DE COMPRESSION - PROJET WILLIAM
# Création archive ZIP optimisée du projet RestroSaaS nettoyé

echo "🎯 CRÉATION ARCHIVE ZIP - PROJET WILLIAM"
echo "========================================"

# Configuration
PROJECT_NAME="william"
DATE=$(date +"%Y%m%d_%H%M%S")
ARCHIVE_NAME="${PROJECT_NAME}_${DATE}.zip"
SOURCE_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
TEMP_DIR="/tmp/william_build"
EXCLUDE_FILE="/tmp/zip_exclude.txt"

echo "📦 Préparation de l'archive..."
echo "Nom: $ARCHIVE_NAME"
echo "Source: $SOURCE_DIR"

# Créer liste d'exclusion
cat > "$EXCLUDE_FILE" << EOF
.git/*
.gitignore
node_modules/*
vendor/*
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/testing/*
storage/framework/views/*
bootstrap/cache/*
.env
.env.local
.env.production
.env.staging
.env.testing
*.log
.DS_Store
Thumbs.db
*.tmp
*.temp
*.swp
*.orig
*.bak
*.backup
*~
reports/*
temp/*
.vscode/
.idea/
*.zip
*.tar.gz
*.rar
composer.lock
package-lock.json
yarn.lock
phpunit.xml
.phpunit.result.cache
.coverage
coverage/
tests/
public/hot
public/storage
mix-manifest.json
EOF

echo "🧹 Nettoyage pré-compression..."

# Nettoyer avant compression
cd "$SOURCE_DIR"

# Supprimer fichiers temporaires
find . -name ".DS_Store" -delete 2>/dev/null || true
find . -name "Thumbs.db" -delete 2>/dev/null || true
find . -name "*.tmp" -delete 2>/dev/null || true
find . -name "*.temp" -delete 2>/dev/null || true
find . -name "*.swp" -delete 2>/dev/null || true

# Nettoyer les logs
rm -rf storage/logs/*.log 2>/dev/null || true

# Nettoyer les caches
rm -rf storage/framework/cache/data/* 2>/dev/null || true
rm -rf storage/framework/sessions/* 2>/dev/null || true
rm -rf storage/framework/views/* 2>/dev/null || true
rm -rf bootstrap/cache/*.php 2>/dev/null || true

echo "📦 Création de l'archive ZIP..."

# Créer l'archive en excluant les fichiers inutiles
cd "$(dirname "$SOURCE_DIR")"
zip -r "$ARCHIVE_NAME" "$(basename "$SOURCE_DIR")" \
    -x@"$EXCLUDE_FILE" \
    -x "*.git*" \
    -x "*node_modules*" \
    -x "*vendor*" \
    -x "*.env" \
    -x "*.env.local" \
    -x "*.env.production" \
    -x "*.env.staging" \
    -x "*.env.testing" \
    -x "*.log" \
    -x "*storage/logs*" \
    -x "*storage/framework/cache*" \
    -x "*storage/framework/sessions*" \
    -x "*storage/framework/testing*" \
    -x "*storage/framework/views*" \
    -x "*bootstrap/cache*" \
    -x "*tests*" \
    -x "*.DS_Store" \
    -x "*Thumbs.db" \
    -x "*.tmp" \
    -x "*.temp" \
    -x "*.swp" \
    -q

if [ $? -eq 0 ]; then
    echo "✅ Archive créée avec succès!"
    echo "📁 Fichier: $ARCHIVE_NAME"
    echo "📍 Emplacement: $(pwd)/$ARCHIVE_NAME"

    # Afficher la taille
    SIZE=$(du -h "$ARCHIVE_NAME" | cut -f1)
    echo "📊 Taille: $SIZE"

    # Afficher le contenu (premiers fichiers)
    echo ""
    echo "📋 Contenu de l'archive (aperçu):"
    unzip -l "$ARCHIVE_NAME" | head -20

    echo ""
    echo "🎉 SUCCÈS! Archive ZIP 'william' créée!"
    echo "🚀 Prêt pour distribution/déploiement"

else
    echo "❌ Erreur lors de la création de l'archive"
    exit 1
fi

# Nettoyer
rm -f "$EXCLUDE_FILE"

echo ""
echo "📦 ARCHIVE WILLIAM TERMINÉE"
echo "=========================="
