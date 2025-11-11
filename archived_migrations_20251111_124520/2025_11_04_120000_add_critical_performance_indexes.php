<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index pour les requêtes d'ordres les plus fréquentes
        $this->addIndexIfNotExists('orders', 'idx_orders_vendor_status_date', [
            'vendor_id', 'status_type', 'created_at'
        ]);

        // Index pour les items par vendor et disponibilité
        $this->addIndexIfNotExists('items', 'idx_items_vendor_available', [
            'vendor_id', 'is_available', 'reorder_id'
        ]);

        // Index pour les détails de commande (N+1 queries)
        $this->addIndexIfNotExists('order_details', 'idx_order_details_order_item', [
            'order_id', 'item_id'
        ]);

        // Index pour le panier utilisateur
        $this->addIndexIfNotExists('carts', 'idx_carts_user_vendor', [
            'user_id', 'vendor_id'
        ]);

        // Index pour les catégories par vendor
        $this->addIndexIfNotExists('categories', 'idx_categories_vendor_status', [
            'vendor_id', 'is_available', 'reorder_id'
        ]);

        // Index pour les paiements
        $this->addIndexIfNotExists('transactions', 'idx_transactions_order_status', [
            'order_id', 'payment_status', 'created_at'
        ]);

        // Index pour les analytics (requêtes temporelles)
        $this->addIndexIfNotExists('orders', 'idx_orders_analytics', [
            'vendor_id', 'created_at', 'grand_total'
        ]);

        // Index pour les clients
        $this->addIndexIfNotExists('customers', 'idx_customers_email_phone', [
            'email', 'mobile'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les index en ordre inverse
        $this->dropIndexIfExists('customers', 'idx_customers_email_phone');
        $this->dropIndexIfExists('orders', 'idx_orders_analytics');
        $this->dropIndexIfExists('transactions', 'idx_transactions_order_status');
        $this->dropIndexIfExists('categories', 'idx_categories_vendor_status');
        $this->dropIndexIfExists('carts', 'idx_carts_user_vendor');
        $this->dropIndexIfExists('order_details', 'idx_order_details_order_item');
        $this->dropIndexIfExists('items', 'idx_items_vendor_available');
        $this->dropIndexIfExists('orders', 'idx_orders_vendor_status_date');
    }

    /**
     * Ajouter un index seulement s'il n'existe pas déjà
     */
    private function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        // Vérifier si la table existe
        if (!Schema::hasTable($table)) {
            echo "⚠️  Table '{$table}' n'existe pas, index ignoré\n";
            return;
        }

        // Vérifier si toutes les colonnes existent
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                echo "⚠️  Colonne '{$column}' n'existe pas dans '{$table}', index ignoré\n";
                return;
            }
        }

        try {
            // Vérifier si l'index existe déjà
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')
                ->toArray();

            if (!in_array($indexName, $indexes)) {
                Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                    $table->index($columns, $indexName);
                });
                echo "✅ Index '{$indexName}' créé sur table '{$table}'\n";
            } else {
                echo "ℹ️  Index '{$indexName}' existe déjà sur table '{$table}'\n";
            }
        } catch (\Exception $e) {
            echo "❌ Erreur lors de la création de l'index '{$indexName}': {$e->getMessage()}\n";
        }
    }

    /**
     * Supprimer un index seulement s'il existe
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')
                ->toArray();

            if (in_array($indexName, $indexes)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
                echo "✅ Index '{$indexName}' supprimé de la table '{$table}'\n";
            }
        } catch (\Exception $e) {
            echo "❌ Erreur lors de la suppression de l'index '{$indexName}': {$e->getMessage()}\n";
        }
    }
};
