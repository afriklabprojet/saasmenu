#!/bin/bash

echo "🔄 Transition vers Migration Fusionnée - RestroSaaS"
echo "=================================================="

cd "$(dirname "$0")"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

print_color() {
    echo -e "${1}${2}${NC}"
}

# Vérifier si la migration fusionnée existe
MERGED_MIGRATION="2024_01_01_000000_create_all_tables"

print_color $BLUE "🔍 Analyse de l'état du système..."

# Vérifier si la migration fusionnée est dans les fichiers
if [[ ! -f "database/migrations/${MERGED_MIGRATION}.php" ]]; then
    print_color $RED "❌ Migration fusionnée introuvable !"
    echo "Exécutez d'abord: php merge-migrations.php"
    exit 1
fi

# Vérifier l'état de la migration fusionnée
migration_status=$(php artisan migrate:status | grep "$MERGED_MIGRATION" || echo "NOT_FOUND")

if [[ "$migration_status" == "NOT_FOUND" ]]; then
    print_color $YELLOW "📊 Migration fusionnée non répertoriée dans la base"

    # Vérifier si les tables principales existent
    tables_exist=$(php artisan tinker --execute="
        \$tables = ['users', 'restaurants', 'orders', 'items'];
        \$existing = 0;
        foreach (\$tables as \$table) {
            if (Schema::hasTable(\$table)) \$existing++;
        }
        echo \$existing;
    ")

    if [[ "$tables_exist" -gt 0 ]]; then
        print_color $YELLOW "⚠️  Tables détectées ($tables_exist/4), marquage de la migration comme exécutée..."

        # Marquer comme exécutée
        php artisan tinker --execute="
            DB::table('migrations')->insert([
                'migration' => '$MERGED_MIGRATION',
                'batch' => 1
            ]);
            echo 'Migration marquée comme exécutée';
        "

        print_color $GREEN "✅ Migration fusionnée marquée comme exécutée"
    else
        print_color $BLUE "🚀 Nouvelles tables - exécution de la migration fusionnée..."
        php artisan migrate --step
    fi

elif [[ "$migration_status" =~ "Pending" ]]; then
    print_color $YELLOW "⏳ Migration fusionnée en attente"

    # Vérifier si les tables existent déjà
    table_users_exists=$(php artisan tinker --execute="echo Schema::hasTable('users') ? 'true' : 'false';")

    if [[ "$table_users_exists" == "true" ]]; then
        print_color $YELLOW "⚠️  Tables existent déjà - marquage comme exécutée..."

        # Marquer comme exécutée sans l'exécuter
        php artisan tinker --execute="
            DB::table('migrations')->where('migration', '$MERGED_MIGRATION')->update(['batch' => 1]);
            echo 'Migration mise à jour';
        "

        print_color $GREEN "✅ Conflit résolu - migration marquée comme exécutée"
    else
        print_color $BLUE "🚀 Exécution de la migration fusionnée..."
        php artisan migrate --step
    fi

elif [[ "$migration_status" =~ "Ran" ]]; then
    print_color $GREEN "✅ Migration fusionnée déjà exécutée"
else
    print_color $RED "❓ État inconnu de la migration"
    echo "Status: $migration_status"
fi

echo ""
print_color $BLUE "📊 État final des migrations:"
php artisan migrate:status | tail -10

echo ""
print_color $BLUE "🧪 Test de validation:"

# Vérifier quelques tables importantes
important_tables=("users" "restaurants" "orders" "items" "loyalty_transactions" "pos_terminals")
all_good=true

for table in "${important_tables[@]}"; do
    exists=$(php artisan tinker --execute="echo Schema::hasTable('$table') ? 'true' : 'false';")
    if [[ "$exists" == "true" ]]; then
        print_color $GREEN "  ✅ Table $table existe"
    else
        print_color $RED "  ❌ Table $table manquante"
        all_good=false
    fi
done

echo ""
if [[ "$all_good" == "true" ]]; then
    print_color $GREEN "🎉 Système validé - toutes les tables importantes sont présentes !"

    echo ""
    print_color $BLUE "🔧 Prochaines étapes recommandées:"
    echo "1. Archiver les anciennes migrations:"
    echo "   ./manage-merged-migration.sh (option 4)"
    echo ""
    echo "2. Tester les addons:"
    echo "   php check-addons-implementation.php"
    echo ""
    echo "3. Optimiser le cache:"
    echo "   php artisan config:cache"
    echo "   php artisan route:cache"

else
    print_color $RED "⚠️  Certaines tables manquent - vérification nécessaire"
fi

echo ""
print_color $GREEN "✅ Transition terminée !"
