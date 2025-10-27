#!/bin/bash

echo "🔄 Gestionnaire de Migration Fusionnée - RestroSaaS"
echo "=================================================="

cd "$(dirname "$0")"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher avec couleur
print_color() {
    echo -e "${1}${2}${NC}"
}

# Vérifier si la migration fusionnée existe
MERGED_MIGRATION="database/migrations/2024_01_01_000000_create_all_tables.php"

if [[ ! -f "$MERGED_MIGRATION" ]]; then
    print_color $RED "❌ Migration fusionnée introuvable !"
    echo "Exécutez d'abord: php merge-migrations.php"
    exit 1
fi

print_color $GREEN "✅ Migration fusionnée trouvée : $MERGED_MIGRATION"

# Menu principal
while true; do
    echo ""
    print_color $BLUE "🔧 Options disponibles :"
    echo "1. 📊 Analyser l'état actuel des migrations"
    echo "2. 💾 Sauvegarder les migrations existantes"
    echo "3. 🧪 Tester la migration fusionnée (dry-run)"
    echo "4. 🗑️  Archiver les anciennes migrations"
    echo "5. 🚀 Appliquer la migration fusionnée (ATTENTION!)"
    echo "6. 🔄 Restaurer les migrations sauvegardées"
    echo "7. ❌ Quitter"
    echo ""
    read -p "Choisissez une option (1-7): " choice

    case $choice in
        1)
            print_color $YELLOW "📊 Analyse de l'état des migrations..."
            echo ""
            echo "Migration fusionnée :"
            ls -la "$MERGED_MIGRATION"
            echo ""
            echo "Nombre de migrations existantes :"
            migration_count=$(ls database/migrations/*.php | grep -v "2024_01_01_000000_create_all_tables.php" | wc -l)
            echo "- Migrations originales : $migration_count"
            echo "- Migration fusionnée : 1"
            echo ""
            echo "État de la base de données :"
            php artisan migrate:status | head -10
            echo "..."
            php artisan migrate:status | tail -5
            ;;

        2)
            print_color $YELLOW "💾 Sauvegarde des migrations..."
            backup_dir="migrations_backup_$(date +%Y%m%d_%H%M%S)"
            mkdir -p "$backup_dir"

            # Copier toutes les migrations sauf la fusionnée
            cp database/migrations/*.php "$backup_dir/" 2>/dev/null
            rm "$backup_dir/2024_01_01_000000_create_all_tables.php" 2>/dev/null

            migration_count=$(ls "$backup_dir"/*.php 2>/dev/null | wc -l)
            print_color $GREEN "✅ $migration_count migrations sauvegardées dans $backup_dir/"

            # Créer un fichier de restauration
            cat > "${backup_dir}/restore.sh" << 'EOF'
#!/bin/bash
echo "🔄 Restauration des migrations..."
cp *.php ../database/migrations/
echo "✅ Migrations restaurées !"
EOF
            chmod +x "${backup_dir}/restore.sh"
            ;;

        3)
            print_color $YELLOW "🧪 Test de la migration fusionnée (simulation)..."
            echo ""
            echo "Contenu de la migration :"
            echo "- $(grep -c 'Schema::create' $MERGED_MIGRATION) tables à créer"
            echo "- $(grep -c 'Schema::dropIfExists' $MERGED_MIGRATION) tables à supprimer (méthode down)"
            echo ""
            echo "Test de syntaxe PHP :"
            if php -l "$MERGED_MIGRATION" > /dev/null 2>&1; then
                print_color $GREEN "✅ Syntaxe PHP valide"
            else
                print_color $RED "❌ Erreur de syntaxe PHP"
                php -l "$MERGED_MIGRATION"
            fi
            ;;

        4)
            print_color $YELLOW "🗑️  Archivage des anciennes migrations..."
            read -p "⚠️  Voulez-vous vraiment archiver toutes les anciennes migrations ? (y/N): " confirm

            if [[ "$confirm" =~ ^[Yy]$ ]]; then
                archive_dir="archived_migrations_$(date +%Y%m%d_%H%M%S)"
                mkdir -p "$archive_dir"

                # Déplacer toutes les migrations sauf la fusionnée
                for file in database/migrations/*.php; do
                    if [[ "$(basename "$file")" != "2024_01_01_000000_create_all_tables.php" ]]; then
                        mv "$file" "$archive_dir/"
                    fi
                done

                archived_count=$(ls "$archive_dir"/*.php 2>/dev/null | wc -l)
                print_color $GREEN "✅ $archived_count migrations archivées dans $archive_dir/"

                echo "Migrations restantes :"
                ls -la database/migrations/
            else
                print_color $BLUE "ℹ️  Archivage annulé"
            fi
            ;;

        5)
            print_color $RED "⚠️  ATTENTION : Application de la migration fusionnée"
            echo ""
            echo "Cette action va :"
            echo "1. Réinitialiser complètement la base de données"
            echo "2. Appliquer uniquement la migration fusionnée"
            echo "3. Perdre toutes les données existantes"
            echo ""
            read -p "Êtes-vous ABSOLUMENT sûr ? Tapez 'CONFIRMER' pour continuer: " confirm

            if [[ "$confirm" == "CONFIRMER" ]]; then
                print_color $YELLOW "🚀 Application de la migration fusionnée..."

                # Sauvegarder d'abord
                php artisan migrate:status > "migration_status_before_$(date +%Y%m%d_%H%M%S).txt"

                # Reset et migration
                php artisan migrate:fresh --force

                if [[ $? -eq 0 ]]; then
                    print_color $GREEN "✅ Migration fusionnée appliquée avec succès !"
                    echo ""
                    echo "Nouvel état :"
                    php artisan migrate:status
                else
                    print_color $RED "❌ Erreur lors de l'application de la migration"
                fi
            else
                print_color $BLUE "ℹ️  Application annulée"
            fi
            ;;

        6)
            print_color $YELLOW "🔄 Restauration des migrations..."
            backup_dirs=($(ls -d migrations_backup_* 2>/dev/null | sort -r))

            if [[ ${#backup_dirs[@]} -eq 0 ]]; then
                print_color $RED "❌ Aucune sauvegarde trouvée"
            else
                echo "Sauvegardes disponibles :"
                for i in "${!backup_dirs[@]}"; do
                    echo "$((i+1)). ${backup_dirs[i]}"
                done

                read -p "Choisissez une sauvegarde (1-${#backup_dirs[@]}): " backup_choice

                if [[ "$backup_choice" =~ ^[0-9]+$ ]] && [[ "$backup_choice" -ge 1 ]] && [[ "$backup_choice" -le ${#backup_dirs[@]} ]]; then
                    selected_backup="${backup_dirs[$((backup_choice-1))]}"

                    # Supprimer la migration fusionnée et restaurer
                    rm "$MERGED_MIGRATION"
                    cp "$selected_backup"/*.php database/migrations/

                    print_color $GREEN "✅ Migrations restaurées depuis $selected_backup"
                else
                    print_color $RED "❌ Choix invalide"
                fi
            fi
            ;;

        7)
            print_color $BLUE "👋 Au revoir !"
            exit 0
            ;;

        *)
            print_color $RED "❌ Option invalide"
            ;;
    esac
done
