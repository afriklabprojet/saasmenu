#!/bin/bash

# Script de nettoyage des migrations dupliquées
# Date: 11 novembre 2025
# Objectif: Supprimer les migrations en doublon identifiées dans l'audit

set -e

PROJECT_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
MIGRATIONS_DIR="$PROJECT_DIR/database/migrations"
BACKUP_DIR="$PROJECT_DIR/archived_migrations_$(date +%Y%m%d_%H%M%S)"

echo "🗑️  Nettoyage des migrations dupliquées"
echo "=========================================="
echo ""

# Créer le répertoire de backup
echo "📦 Création du backup dans: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# Fonction pour déplacer une migration vers le backup
move_to_backup() {
    local file="$1"
    local reason="$2"
    if [ -f "$MIGRATIONS_DIR/$file" ]; then
        echo "  ➜ Archivage: $file ($reason)"
        mv "$MIGRATIONS_DIR/$file" "$BACKUP_DIR/"
    else
        echo "  ⚠️  Fichier non trouvé: $file"
    fi
}

echo ""
echo "🔍 Migrations dupliquées identifiées:"
echo ""

# 1. Migrations d'indexes en doublon (garder la plus récente et complète)
echo "1️⃣  Migrations d'indexes de performance (5 fichiers)"
move_to_backup "2025_11_04_030000_add_performance_indexes.php" "Doublon - version obsolète"
move_to_backup "2025_11_04_030001_add_performance_indexes_only.php" "Doublon - version incomplète"
move_to_backup "2025_11_04_030002_add_safe_performance_indexes.php" "Doublon - version intermédiaire"
move_to_backup "2025_11_04_120000_add_critical_performance_indexes.php" "Doublon - version intermédiaire"
# Garder: 2025_11_04_130000_add_corrected_performance_indexes.php (la plus récente)

echo ""
echo "2️⃣  Migrations seo_meta en doublon (2 fichiers)"
move_to_backup "2025_10_25_043341_create_seo_meta_table.php" "Doublon - version sans 's'"
# Garder: 2025_10_25_114415_create_seo_metas_table.php (version correcte)

echo ""
echo "✅ Nettoyage terminé!"
echo ""
echo "📊 Résumé:"
echo "  - Migrations archivées: $(ls -1 $BACKUP_DIR/*.php 2>/dev/null | wc -l)"
echo "  - Migrations restantes: $(ls -1 $MIGRATIONS_DIR/*.php | wc -l)"
echo "  - Backup location: $BACKUP_DIR"
echo ""
echo "🔄 Pour restaurer les migrations (en cas de problème):"
echo "   cp $BACKUP_DIR/*.php $MIGRATIONS_DIR/"
echo ""
echo "🧪 Prochaine étape: Exécuter les migrations"
echo "   php artisan migrate:fresh --seed"
echo ""
