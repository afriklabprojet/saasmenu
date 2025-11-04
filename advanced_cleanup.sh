#!/bin/bash

# Script de nettoyage avancé RestroSaaS
# Date: 4 novembre 2025

echo "🧹 Nettoyage avancé RestroSaaS - Phase 2"
echo "========================================"

# Définir le répertoire de travail
PROJECT_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
cd "$PROJECT_DIR"

# Créer un dossier d'archive pour les gros fichiers supprimés
ARCHIVE_DIR="./deep_cleanup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$ARCHIVE_DIR"

echo "📂 Dossier d'archive créé: $ARCHIVE_DIR"

# Compteurs
space_before=$(du -sh . | cut -f1)
files_cleaned=0
space_freed=0

echo "💾 Taille actuelle du projet: $space_before"
echo ""

# 1. Nettoyer les logs anciens (garder seulement aujourd'hui)
echo "📋 Nettoyage des logs anciens..."
if [ -d "storage/logs" ]; then
    # Archiver les logs anciens
    find storage/logs -name "*.log" -not -name "*$(date +%Y-%m-%d)*" -exec mv {} "$ARCHIVE_DIR/" \;
    logs_cleaned=$(find "$ARCHIVE_DIR" -name "*.log" | wc -l | tr -d ' ')
    echo "  ✅ $logs_cleaned fichiers de logs archivés"
    files_cleaned=$((files_cleaned + logs_cleaned))
fi

# 2. Nettoyer le cache Laravel complet
echo "🔄 Nettoyage complet du cache Laravel..."
if [ -d "storage/framework/cache" ]; then
    cache_files=$(find storage/framework/cache -type f | wc -l | tr -d ' ')
    find storage/framework/cache -type f -delete 2>/dev/null
    echo "  ✅ $cache_files fichiers de cache supprimés"
    files_cleaned=$((files_cleaned + cache_files))
fi

# 3. Nettoyer les sessions anciennes
echo "🔐 Nettoyage des sessions..."
if [ -d "storage/framework/sessions" ]; then
    session_files=$(find storage/framework/sessions -type f | wc -l | tr -d ' ')
    find storage/framework/sessions -type f -delete 2>/dev/null
    echo "  ✅ $session_files fichiers de session supprimés"
    files_cleaned=$((files_cleaned + session_files))
fi

# 4. Nettoyer les vues compilées
echo "👁️  Nettoyage des vues compilées..."
if [ -d "storage/framework/views" ]; then
    view_files=$(find storage/framework/views -name "*.php" | wc -l | tr -d ' ')
    find storage/framework/views -name "*.php" -delete 2>/dev/null
    echo "  ✅ $view_files vues compilées supprimées"
    files_cleaned=$((files_cleaned + view_files))
fi

# 5. Nettoyer les fichiers .phpunit.cache
echo "🧪 Nettoyage des caches de test..."
find . -name ".phpunit.cache" -delete 2>/dev/null
find . -name ".phpunit.result.cache" -delete 2>/dev/null
echo "  ✅ Caches PHPUnit supprimés"

# 6. Nettoyer les fichiers temporaires système
echo "🗑️  Nettoyage des fichiers temporaires..."
find . -name ".DS_Store" -delete 2>/dev/null
find . -name "Thumbs.db" -delete 2>/dev/null
find . -name "*.tmp" -delete 2>/dev/null
find . -name "*~" -delete 2>/dev/null
echo "  ✅ Fichiers temporaires système supprimés"

# 7. Optimiser les images dupliquées (si elles existent)
echo "🖼️  Analyse des images dupliquées..."
if [ -d "storage/app/public" ]; then
    # Trouver les images potentiellement dupliquées par taille
    duplicate_images=$(find storage/app/public -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" | xargs -I {} sh -c 'echo "$(stat -f%z "{}"):{}"' | sort | uniq -d -w10)
    if [ ! -z "$duplicate_images" ]; then
        echo "$duplicate_images" > "$ARCHIVE_DIR/potential_duplicate_images.txt"
        echo "  ⚠️  Images potentiellement dupliquées listées dans l'archive"
    else
        echo "  ✅ Aucune image dupliquée détectée"
    fi
fi

# 8. Nettoyer les fichiers de build anciens
echo "🔨 Nettoyage des fichiers de build..."
if [ -d "public/build" ]; then
    build_size=$(du -sh public/build | cut -f1)
    rm -rf public/build
    echo "  ✅ Dossier public/build supprimé ($build_size)"
fi

# 9. Nettoyer node_modules si développement fini
echo "📦 Analyse de node_modules..."
if [ -d "node_modules" ]; then
    node_size=$(du -sh node_modules | cut -f1)
    echo "  📊 Taille node_modules: $node_size"
    echo "  💡 Pour nettoyer: rm -rf node_modules (puis npm install pour redévelopper)"
fi

# 10. Analyser vendor pour les packages inutiles
echo "📚 Analyse du dossier vendor..."
if [ -d "vendor" ]; then
    vendor_size=$(du -sh vendor | cut -f1)
    echo "  📊 Taille vendor: $vendor_size"

    # Identifier les packages de développement potentiellement inutiles
    dev_packages=$(find vendor -maxdepth 2 -type d -name "*test*" -o -name "*dev*" -o -name "*debug*" | head -5)
    if [ ! -z "$dev_packages" ]; then
        echo "  💡 Packages de dev détectés (vérifier composer.json):"
        echo "$dev_packages"
    fi
fi

# 11. Nettoyer les fichiers IDE
echo "💻 Nettoyage des fichiers IDE..."
find . -name ".vscode" -type d -exec rm -rf {} + 2>/dev/null
find . -name ".idea" -type d -exec rm -rf {} + 2>/dev/null
find . -name "*.swp" -delete 2>/dev/null
find . -name "*.swo" -delete 2>/dev/null
echo "  ✅ Fichiers IDE nettoyés"

# 12. Optimiser le dossier .git
echo "🔀 Analyse du dossier .git..."
if [ -d ".git" ]; then
    git_size=$(du -sh .git | cut -f1)
    echo "  📊 Taille .git: $git_size"
    echo "  💡 Pour optimiser: git gc --aggressive --prune=now"

    # Nettoyer les références orphelines
    git reflog expire --expire=now --all 2>/dev/null
    git gc --prune=now 2>/dev/null
    echo "  ✅ Git nettoyé (références orphelines supprimées)"
fi

# Calculer l'espace libéré
echo ""
echo "📊 Calcul de l'espace libéré..."
space_after=$(du -sh . | cut -f1)
archive_size=$(du -sh "$ARCHIVE_DIR" | cut -f1)

echo ""
echo "==============================================="
echo "📈 RAPPORT DE NETTOYAGE AVANCÉ"
echo "==============================================="
echo "📦 Fichiers traités: $files_cleaned"
echo "💾 Taille avant: $space_before"
echo "💾 Taille après: $space_after"
echo "🗄️  Archive créée: $ARCHIVE_DIR ($archive_size)"
echo ""

echo "🎯 RECOMMANDATIONS POUR PLUS D'ESPACE:"
echo ""
echo "1. 📦 node_modules (${node_size:-'N/A'}):"
echo "   rm -rf node_modules"
echo "   (npm install quand besoin de redévelopper)"
echo ""
echo "2. 📚 vendor (${vendor_size:-'N/A'}):"
echo "   composer install --no-dev --optimize-autoloader"
echo "   (pour production uniquement)"
echo ""
echo "3. 🔀 .git (${git_size:-'N/A'}):"
echo "   git gc --aggressive --prune=now"
echo ""
echo "4. 🖼️  Images:"
echo "   Vérifier storage/app/public pour images inutiles"
echo ""

# Créer un script pour le nettoyage de production
cat > production_cleanup.sh << 'EOF'
#!/bin/bash
# Nettoyage pour production (ATTENTION: supprime node_modules et dev dependencies)
echo "⚠️  NETTOYAGE PRODUCTION - Suppression node_modules et dev dependencies"
read -p "Confirmer? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    rm -rf node_modules
    composer install --no-dev --optimize-autoloader --no-interaction
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "✅ Optimisé pour production"
else
    echo "❌ Annulé"
fi
EOF

chmod +x production_cleanup.sh
echo "📄 Script production_cleanup.sh créé"

echo ""
echo "✅ Nettoyage avancé terminé!"
echo "🚀 Projet optimisé pour le développement"
