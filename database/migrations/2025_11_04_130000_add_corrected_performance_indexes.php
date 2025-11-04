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
        echo "🚀 Création des index de performance critiques...\n";

        // Index pour les commandes par vendor et statut
        $this->addIndexIfNotExists('orders', 'idx_orders_vendor_status_date', [
            'vendor_id', 'status', 'created_at'
        ]);

        // Index pour les produits par vendor
        $this->addIndexIfNotExists('products', 'idx_products_vendor_status', [
            'vendor_id', 'is_available', 'reorder_id'
        ]);

        // Index pour les détails de commande
        $this->addIndexIfNotExists('order_details', 'idx_order_details_order_product', [
            'order_id', 'product_id'
        ]);

        // Index pour les catégories
        $this->addIndexIfNotExists('categories', 'idx_categories_vendor_available', [
            'vendor_id', 'is_available'
        ]);

        // Index pour les paiements
        $this->addIndexIfNotExists('payments', 'idx_payments_order_status', [
            'order_id', 'payment_status', 'created_at'
        ]);

        // Index pour les utilisateurs (clients)
        $this->addIndexIfNotExists('users', 'idx_users_email_type', [
            'email', 'type'
        ]);

        // Index pour les promocodes
        $this->addIndexIfNotExists('promocodes', 'idx_promocodes_vendor_active', [
            'vendor_id', 'is_available', 'start_date', 'end_date'
        ]);

        // Index pour les favoris
        $this->addIndexIfNotExists('favorites', 'idx_favorites_user_vendor', [
            'user_id', 'vendor_id'
        ]);

        // Index pour les réservations
        $this->addIndexIfNotExists('bookings', 'idx_bookings_vendor_date', [
            'vendor_id', 'date', 'status'
        ]);

        echo "✅ Index de performance créés avec succès !\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "🔄 Suppression des index de performance...\n";

        $this->dropIndexIfExists('bookings', 'idx_bookings_vendor_date');
        $this->dropIndexIfExists('favorites', 'idx_favorites_user_vendor');
        $this->dropIndexIfExists('promocodes', 'idx_promocodes_vendor_active');
        $this->dropIndexIfExists('users', 'idx_users_email_type');
        $this->dropIndexIfExists('payments', 'idx_payments_order_status');
        $this->dropIndexIfExists('categories', 'idx_categories_vendor_available');
        $this->dropIndexIfExists('order_details', 'idx_order_details_order_product');
        $this->dropIndexIfExists('products', 'idx_products_vendor_status');
        $this->dropIndexIfExists('orders', 'idx_orders_vendor_status_date');

        echo "✅ Index supprimés avec succès !\n";
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
                echo "✅ Index '{$indexName}' créé sur table '{$table}' (colonnes: " . implode(', ', $columns) . ")\n";
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
