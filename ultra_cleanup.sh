#!/bin/bash

# Script de nettoyage ultra-agressif RestroSaaS
# ATTENTION: Ce script libère beaucoup d'espace mais peut affecter le développement
# Date: 4 novembre 2025

echo "⚠️  NETTOYAGE ULTRA-AGRESSIF RestroSaaS"
echo "======================================="
echo "🚨 ATTENTION: Ce nettoyage est très agressif!"
echo "📋 Il va supprimer:"
echo "   - node_modules (28M)"
echo "   - Fichiers de cache volumineux"
echo "   - Assets redondants"
echo "   - Optimiser .git"
echo ""

read -p "Voulez-vous continuer? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Nettoyage annulé"
    exit 1
fi

# Définir le répertoire de travail
PROJECT_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
cd "$PROJECT_DIR"

# Créer un dossier d'archive pour les gros fichiers
ULTRA_ARCHIVE="./ultra_cleanup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$ULTRA_ARCHIVE"

echo "📂 Archive ultra: $ULTRA_ARCHIVE"

# Mesurer l'espace avant
space_before=$(du -sh . | cut -f1)
echo "💾 Taille avant: $space_before"

# 1. Supprimer node_modules complètement
echo ""
echo "📦 Suppression node_modules..."
if [ -d "node_modules" ]; then
    node_size=$(du -sh node_modules | cut -f1)
    mv node_modules "$ULTRA_ARCHIVE/"
    echo "  ✅ node_modules ($node_size) archivé"
    echo "  💡 Pour restaurer: mv $ULTRA_ARCHIVE/node_modules ."
    echo "  💡 Pour réinstaller: npm install"
else
    echo "  ℹ️  node_modules déjà absent"
fi

# 2. Optimiser agressivement .git
echo ""
echo "🔀 Optimisation agressive .git..."
if [ -d ".git" ]; then
    git_size_before=$(du -sh .git | cut -f1)

    # Nettoyer l'historique Git agressivement
    git reflog expire --expire=now --all
    git gc --aggressive --prune=now
    git repack -a -d --depth=250 --window=250

    git_size_after=$(du -sh .git | cut -f1)
    echo "  ✅ .git optimisé: $git_size_before → $git_size_after"
fi

# 3. Nettoyer les assets redondants
echo ""
echo "🎨 Nettoyage des assets redondants..."

# Archiver les gros dossiers d'assets moins critiques
if [ -d "storage/app/public/web-assets" ]; then
    web_assets_size=$(du -sh storage/app/public/web-assets | cut -f1)
    echo "  📊 web-assets détecté: $web_assets_size"
    echo "  💡 Conserver pour le moment (assets frontend critiques)"
fi

if [ -d "storage/app/public/admin-assets" ]; then
    admin_assets_size=$(du -sh storage/app/public/admin-assets | cut -f1)
    echo "  📊 admin-assets détecté: $admin_assets_size"
    echo "  💡 Conserver pour le moment (assets admin critiques)"
fi

# Nettoyer les fichiers SQL volumineux non critiques
if [ -f "storage/app/public/restro_saas.sql" ]; then
    sql_size=$(du -sh storage/app/public/restro_saas.sql | cut -f1)
    mv storage/app/public/restro_saas.sql "$ULTRA_ARCHIVE/"
    echo "  ✅ restro_saas.sql ($sql_size) archivé"
fi

# 4. Optimiser vendor pour production
echo ""
echo "📚 Optimisation vendor..."
vendor_size_before=$(du -sh vendor | cut -f1)

# Nettoyer le cache Composer
composer clear-cache

# Optimiser l'autoloader
composer dump-autoload --optimize --classmap-authoritative

vendor_size_after=$(du -sh vendor | cut -f1)
echo "  ✅ vendor optimisé: $vendor_size_before → $vendor_size_after"

# 5. Compresser les images si possible
echo ""
echo "🖼️  Optimisation des images..."
image_count=0

# Optimiser les images PNG (si disponible)
if command -v pngquant >/dev/null 2>&1; then
    find storage/app/public -name "*.png" -exec pngquant --force --ext .png --quality=60-80 {} \; 2>/dev/null
    image_count=$(find storage/app/public -name "*.png" | wc -l | tr -d ' ')
    echo "  ✅ $image_count images PNG optimisées"
else
    echo "  ⚠️  pngquant non disponible pour optimiser les PNG"
fi

# 6. Nettoyer les logs de développement
echo ""
echo "📋 Suppression complète des logs..."
if [ -d "storage/logs" ]; then
    logs_size=$(du -sh storage/logs | cut -f1)
    find storage/logs -name "*.log" -delete
    echo "  ✅ Tous les logs supprimés ($logs_size libéré)"
fi

# 7. Supprimer les caches de développement
echo ""
echo "💾 Suppression des caches de développement..."

# Cache Laravel complet
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Cache Composer
rm -rf ~/.composer/cache 2>/dev/null

echo "  ✅ Tous les caches supprimés"

# 8. Créer un fichier de restauration
echo ""
echo "📄 Création du script de restauration..."

cat > restore_from_ultra_cleanup.sh << EOF
#!/bin/bash
# Script de restauration après nettoyage ultra-agressif

echo "🔄 Restauration après nettoyage ultra-agressif"
echo "============================================="

# Restaurer node_modules si archivé
if [ -d "$ULTRA_ARCHIVE/node_modules" ]; then
    echo "📦 Restauration node_modules..."
    mv "$ULTRA_ARCHIVE/node_modules" .
    echo "  ✅ node_modules restauré"
else
    echo "📦 Installation node_modules..."
    npm install
    echo "  ✅ node_modules installé"
fi

# Restaurer le fichier SQL si nécessaire
if [ -f "$ULTRA_ARCHIVE/restro_saas.sql" ]; then
    echo "🗄️  Restaurer restro_saas.sql? (y/N)"
    read -n 1 -r
    if [[ \$REPLY =~ ^[Yy]$ ]]; then
        mv "$ULTRA_ARCHIVE/restro_saas.sql" storage/app/public/
        echo "  ✅ restro_saas.sql restauré"
    fi
fi

# Reconstruire les caches
echo "🔄 Reconstruction des caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload

echo "✅ Restauration terminée!"
EOF

chmod +x restore_from_ultra_cleanup.sh

# Mesurer l'espace final
space_after=$(du -sh . | cut -f1)
archive_size=$(du -sh "$ULTRA_ARCHIVE" | cut -f1)

echo ""
echo "==============================================="
echo "🚀 RAPPORT NETTOYAGE ULTRA-AGRESSIF"
echo "==============================================="
echo "💾 Taille avant: $space_before"
echo "💾 Taille après: $space_after"
echo "🗄️  Archive: $ULTRA_ARCHIVE ($archive_size)"
echo ""
echo "✅ FICHIERS SUPPRIMÉS/ARCHIVÉS:"
echo "  📦 node_modules → archivé"
echo "  🗄️  restro_saas.sql → archivé"
echo "  📋 logs → supprimés"
echo "  💾 caches → supprimés"
echo "  🔀 .git → optimisé"
echo ""
echo "🔄 POUR RESTAURER:"
echo "  ./restore_from_ultra_cleanup.sh"
echo ""
echo "🎯 POUR DÉVELOPPEMENT:"
echo "  npm install (réinstaller node_modules)"
echo "  php artisan serve (démarrer serveur)"
echo ""
echo "✅ Nettoyage ultra-agressif terminé!"

# Calculer l'espace libéré approximatif
echo "💡 Espace libéré: ~$(echo "$space_before" | sed 's/M//')-$(echo "$space_after" | sed 's/M//')M (approximatif)"
