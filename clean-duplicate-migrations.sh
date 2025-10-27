#!/bin/bash

echo "🔍 Recherche des migrations en double..."

cd "$(dirname "$0")"

# Fonction pour détecter les migrations en double
detect_duplicates() {
    echo "Analyse des fichiers de migration..."

    # Créer un fichier temporaire pour stocker les noms de table
    temp_file=$(mktemp)

    # Extraire les noms de table de toutes les migrations
    for migration in database/migrations/*.php; do
        if [[ -f "$migration" ]]; then
            table_name=$(basename "$migration" | sed -E 's/^[0-9_]+_create_([^_]+)_table\.php$/\1/' | sed 's/_table//')
            if [[ "$table_name" != "$(basename "$migration")" ]]; then
                echo "$table_name:$migration" >> "$temp_file"
            fi
        fi
    done

    # Identifier les doublons
    duplicates=$(cat "$temp_file" | cut -d: -f1 | sort | uniq -d)

    if [[ -n "$duplicates" ]]; then
        echo "⚠️  Migrations en double détectées :"
        for table in $duplicates; do
            echo "Table: $table"
            grep "^$table:" "$temp_file" | cut -d: -f2
            echo ""
        done

        # Proposer de supprimer les doublons
        echo "Voulez-vous supprimer automatiquement les doublons ? (y/N)"
        read -r response
        if [[ "$response" =~ ^[Yy]$ ]]; then
            for table in $duplicates; do
                files=($(grep "^$table:" "$temp_file" | cut -d: -f2))
                if [[ ${#files[@]} -gt 1 ]]; then
                    # Garder le plus ancien, supprimer les autres
                    echo "Suppression des doublons pour la table $table..."
                    for ((i=1; i<${#files[@]}; i++)); do
                        migration_name=$(basename "${files[i]}" .php)
                        echo "Suppression de ${files[i]}"
                        rm "${files[i]}"

                        # Supprimer de la base de données
                        php artisan tinker --execute="DB::table('migrations')->where('migration', '$migration_name')->delete(); echo 'Record deleted for $migration_name';"
                    done
                fi
            done
        fi
    else
        echo "✅ Aucune migration en double détectée"
    fi

    rm "$temp_file"
}

# Vérifier l'état des migrations
echo "📊 État actuel des migrations :"
php artisan migrate:status

echo ""
detect_duplicates

echo ""
echo "🧪 Test des migrations..."
if php artisan migrate --pretend > /dev/null 2>&1; then
    echo "✅ Toutes les migrations sont prêtes"
else
    echo "❌ Problèmes détectés dans les migrations"
    php artisan migrate --pretend
fi

echo ""
echo "🎯 Nettoyage terminé !"
