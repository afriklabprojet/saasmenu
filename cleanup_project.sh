#!/bin/bash

# Script de nettoyage des fichiers obsolètes RestroSaaS
# Date: 4 novembre 2025

echo "🧹 Nettoyage des fichiers obsolètes RestroSaaS"
echo "=============================================="

# Définir le répertoire de travail
PROJECT_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
cd "$PROJECT_DIR"

# Créer un dossier d'archive pour les fichiers supprimés (au cas où)
ARCHIVE_DIR="./archived_files_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$ARCHIVE_DIR"

echo "📂 Dossier d'archive créé: $ARCHIVE_DIR"

# Fichiers markdown à supprimer (garder seulement les rapports finaux essentiels)
FILES_TO_REMOVE=(
    # Documentation de développement obsolète
    "INFRASTRUCTURE_REPAIR_REPORT.md"
    "REFACTORING_MIGRATION_GUIDE.md"
    "REPOSITORY_METHODS_COMPLETION.md"
    "RESOLUTION_LANGUAGES_TABLE.md"
    "RESOLUTION_PRICING_PLANS_FINALE.md"
    "RESOLUTION_SYSTEMADDONS_FINALE.md"
    "SECURITY_FIXES_REPORT.md"
    "TESTS_AUTOMATISES_RAPPORT.md"
    "TESTS_FONCTIONNELS_RAPPORT.md"
    "TRANSFORMATION_REPORT.md"
    "validation_routes_refactorisees.md"
    "MIGRATION_REALISTE_LARAVEL11.md"
    "LARAVEL12_MIGRATION_PLAN.md"

    # Scripts de test obsolètes
    "test_endpoints.sh"
    "security_check.php"
    "fix_languages_table.php"
    "test_refactored_routes.php"

    # Fichiers SQL de développement
    "add-store-categories.sql"
    "fix-ville-zone-defaults.sql"
    "willy2.sql"

    # Audit temporaires
    "audits/update.audit.md"
    "audits/maintenability.md"
)

# Compteurs
removed_count=0
archived_count=0

echo "🗑️  Suppression des fichiers obsolètes..."

for file in "${FILES_TO_REMOVE[@]}"; do
    if [ -f "$file" ]; then
        echo "  📄 Archivage: $file"

        # Créer le répertoire dans l'archive si nécessaire
        archive_subdir="$ARCHIVE_DIR/$(dirname "$file")"
        mkdir -p "$archive_subdir"

        # Déplacer vers l'archive
        mv "$file" "$ARCHIVE_DIR/$file"

        archived_count=$((archived_count + 1))
        echo "    ✅ Archivé vers $ARCHIVE_DIR/$file"
    else
        echo "  ⚠️  Fichier non trouvé: $file"
    fi
done

# Nettoyer les dossiers vides
echo "🧹 Nettoyage des dossiers vides..."

# Supprimer le dossier audits s'il est vide
if [ -d "audits" ] && [ -z "$(ls -A audits)" ]; then
    rmdir "audits"
    echo "  🗂️  Dossier 'audits' vide supprimé"
fi

# Nettoyer les fichiers cache et temporaires
echo "🧽 Nettoyage des fichiers cache et temporaires..."

# Cache Laravel
if [ -d "bootstrap/cache" ]; then
    find bootstrap/cache -name "*.php" -type f -delete 2>/dev/null
    echo "  🔄 Cache bootstrap nettoyé"
fi

# Logs anciens (garder seulement les 7 derniers jours)
if [ -d "storage/logs" ]; then
    find storage/logs -name "*.log" -type f -mtime +7 -delete 2>/dev/null
    echo "  📋 Logs anciens supprimés"
fi

# Cache de vues
if [ -d "storage/framework/views" ]; then
    find storage/framework/views -name "*.php" -type f -delete 2>/dev/null
    echo "  👁️  Cache de vues nettoyé"
fi

# Sessions anciennes
if [ -d "storage/framework/sessions" ]; then
    find storage/framework/sessions -type f -mtime +1 -delete 2>/dev/null
    echo "  🔐 Sessions anciennes supprimées"
fi

# Nettoyer les fichiers .DS_Store (macOS)
find . -name ".DS_Store" -delete 2>/dev/null
echo "  🍎 Fichiers .DS_Store supprimés"

# Rapports finaux conservés
echo ""
echo "📋 Fichiers de documentation conservés:"
echo "  ✅ README.md (documentation principale)"
echo "  ✅ ANALYTICS_BI_DASHBOARD_RAPPORT_FINAL.md (Priorité 2)"
echo "  ✅ BACKGROUND_JOBS_RAPPORT_FINAL.md (Priorité 1)"
echo "  ✅ FRONTEND_PERFORMANCE_RAPPORT_FINAL.md (Priorité 3)"
echo "  ✅ MONITORING_LOGGING_RAPPORT_FINAL.md (Monitoring)"

echo ""
echo "📊 Résumé du nettoyage:"
echo "  📦 Fichiers archivés: $archived_count"
echo "  🗂️  Dossier d'archive: $ARCHIVE_DIR"
echo ""
echo "💡 Les fichiers archivés peuvent être restaurés depuis: $ARCHIVE_DIR"
echo ""

# Afficher la taille économisée
if [ $archived_count -gt 0 ]; then
    archive_size=$(du -sh "$ARCHIVE_DIR" | cut -f1)
    echo "💾 Espace libéré: $archive_size"
else
    echo "ℹ️  Aucun fichier à nettoyer trouvé"
fi

echo ""
echo "✅ Nettoyage terminé! Projet RestroSaaS optimisé."
echo "🚀 Les 3 priorités (Background Jobs, Analytics, Performance) restent intactes."
