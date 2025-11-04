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
