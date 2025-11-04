#!/bin/bash
# Script de restauration après nettoyage ultra-agressif

echo "🔄 Restauration après nettoyage ultra-agressif"
echo "============================================="

# Restaurer node_modules si archivé
if [ -d "./ultra_cleanup_20251104_184439/node_modules" ]; then
    echo "📦 Restauration node_modules..."
    mv "./ultra_cleanup_20251104_184439/node_modules" .
    echo "  ✅ node_modules restauré"
else
    echo "📦 Installation node_modules..."
    npm install
    echo "  ✅ node_modules installé"
fi

# Restaurer le fichier SQL si nécessaire
if [ -f "./ultra_cleanup_20251104_184439/restro_saas.sql" ]; then
    echo "🗄️  Restaurer restro_saas.sql? (y/N)"
    read -n 1 -r
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        mv "./ultra_cleanup_20251104_184439/restro_saas.sql" storage/app/public/
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
