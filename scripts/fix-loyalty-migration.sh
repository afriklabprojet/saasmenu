#!/bin/bash

# 🔧 SCRIPT DE CORRECTION - CONFLIT MIGRATION LOYALTY_TRANSACTIONS
# Résout l'erreur de table déjà existante

echo "🔧 CORRECTION CONFLIT MIGRATION - LOYALTY_TRANSACTIONS"
echo "======================================================="

# Configuration
MIGRATION_FILE="2024_01_15_000012_create_loyalty_transactions_table"
TABLE_NAME="loyalty_transactions"

echo "📋 Problème détecté:"
echo "   - Migration: $MIGRATION_FILE"
echo "   - Table: $TABLE_NAME déjà existante"
echo "   - Erreur: SQLSTATE[42S01] Table already exists"

echo ""
echo "🎯 Solutions disponibles:"
echo "1. Marquer la migration comme exécutée (recommandé)"
echo "2. Supprimer la table existante et refaire la migration"
echo "3. Analyser les différences de structure"

echo ""
read -p "Choisissez une option (1-3): " choice

case $choice in
    1)
        echo "✅ Option 1: Marquer la migration comme exécutée"
        echo "   Insertion dans la table migrations..."

        # Marquer la migration comme exécutée sans l'exécuter
        php artisan tinker --execute="
            DB::table('migrations')->insert([
                'migration' => '$MIGRATION_FILE',
                'batch' => DB::table('migrations')->max('batch') + 1
            ]);
            echo 'Migration marquée comme exécutée\n';
        "

        if [ $? -eq 0 ]; then
            echo "✅ Migration marquée comme exécutée avec succès!"
            echo "🔄 Tentative de continuer les migrations..."
            php artisan migrate
        else
            echo "❌ Erreur lors du marquage de la migration"
        fi
        ;;

    2)
        echo "⚠️  Option 2: Supprimer et recréer la table"
        echo "   ATTENTION: Ceci supprimera toutes les données de loyalty_transactions!"
        read -p "   Êtes-vous sûr? (oui/non): " confirm

        if [ "$confirm" = "oui" ]; then
            echo "🗑️  Suppression de la table..."
            php artisan tinker --execute="
                Schema::dropIfExists('$TABLE_NAME');
                echo 'Table supprimée\n';
            "

            echo "🔄 Relance des migrations..."
            php artisan migrate
        else
            echo "❌ Opération annulée"
        fi
        ;;

    3)
        echo "🔍 Option 3: Analyse de la structure"
        echo "   Structure actuelle de la table:"

        php artisan tinker --execute="
            \$columns = DB::select('DESCRIBE $TABLE_NAME');
            foreach(\$columns as \$column) {
                echo \$column->Field . ' | ' . \$column->Type . ' | ' . \$column->Null . ' | ' . \$column->Key . '\n';
            }
        "

        echo ""
        echo "📋 Pour comparaison, voici la structure attendue par la migration:"
        echo "   - id: bigint unsigned auto_increment primary key"
        echo "   - member_id: bigint unsigned not null"
        echo "   - restaurant_id: bigint unsigned not null"
        echo "   - order_id: int unsigned null"
        echo "   - type: enum(...) not null"
        echo "   - points: int not null"
        echo "   - balance_after: int not null"
        echo "   - description: text not null"
        echo "   - expires_at: timestamp null"
        echo "   - expired_at: timestamp null"
        echo "   - metadata: json null"
        echo "   - created_at: timestamp null"
        echo "   - updated_at: timestamp null"

        echo ""
        echo "💡 Après comparaison, utilisez l'option 1 ou 2"
        ;;

    *)
        echo "❌ Option invalide"
        exit 1
        ;;
esac

echo ""
echo "🧪 Test final - Vérification des migrations:"
php artisan migrate:status | grep loyalty

echo ""
echo "✅ CORRECTION TERMINÉE"
echo "====================="
