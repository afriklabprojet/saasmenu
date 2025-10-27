#!/bin/bash

# Script de dump de base de données - KOFFI
# Date: $(date +%Y-%m-%d)

DB_NAME="c2687072c_restooo225"
DB_USER="c2687072c_paulin225"
DB_PASS='7)2GRB~eZ#IiBr.Q'
DB_HOST="127.0.0.1"
BACKUP_FILE="koffi_$(date +%Y%m%d_%H%M%S).sql"

echo "🔧 Création du dump de la base de données..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Database: $DB_NAME"
echo "Fichier: $BACKUP_FILE"
echo ""

# Tentative avec mysqldump
if command -v mysqldump &> /dev/null; then
    mysqldump -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" "$DB_NAME" > "$BACKUP_FILE"
    
    if [ $? -eq 0 ]; then
        echo "✅ Dump créé avec succès!"
        echo "📁 Fichier: database/backups/$BACKUP_FILE"
        ls -lh "$BACKUP_FILE"
    else
        echo "⚠️  Erreur mysqldump - Vérifiez que la base existe sur le serveur"
    fi
else
    echo "⚠️  mysqldump non trouvé"
    echo "💡 Utilisez cette commande sur le serveur de production:"
    echo ""
    echo "mysqldump -u $DB_USER -p'$DB_PASS' $DB_NAME > koffi_backup.sql"
fi

