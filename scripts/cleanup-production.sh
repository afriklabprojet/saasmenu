#!/bin/bash

# Script de nettoyage complet RestroSaaS
# Utilisé pour nettoyer le projet avant mise en production

echo "🧹 Nettoyage RestroSaaS - Production Ready"
echo "======================================="

# Nettoyage des caches Laravel
echo "📦 Nettoyage des caches Laravel..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Nettoyage des logs anciens
echo "📋 Nettoyage des logs..."
find storage/logs -name "*.log" -type f -mtime +7 -delete 2>/dev/null || true

# Nettoyage des fichiers temporaires
echo "🗑️  Suppression des fichiers temporaires..."
find . -name "*.tmp" -delete 2>/dev/null || true
find . -name "*.temp" -delete 2>/dev/null || true
find . -name "*.bak" -delete 2>/dev/null || true
find . -name "*~" -delete 2>/dev/null || true
find . -name ".DS_Store" -delete 2>/dev/null || true

# Nettoyage des caches Composer
echo "🎼 Nettoyage cache Composer..."
composer clear-cache

# Optimisation pour production
echo "⚡ Optimisation pour production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Nettoyage terminé avec succès !"
echo "🚀 RestroSaaS prêt pour la production"
