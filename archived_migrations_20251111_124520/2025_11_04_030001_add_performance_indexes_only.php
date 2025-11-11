<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexesOnly extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orders table - Critical for performance
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->indexExists('orders', 'idx_orders_vendor_status_created')) {
                $table->index(['vendor_id', 'status', 'created_at'], 'idx_orders_vendor_status_created');
            }

            if (!$this->indexExists('orders', 'idx_orders_user_vendor')) {
                $table->index(['user_id', 'vendor_id'], 'idx_orders_user_vendor');
            }

            if (!$this->indexExists('orders', 'idx_orders_status_created')) {
                $table->index(['status', 'created_at'], 'idx_orders_status_created');
            }
        });

        // Categories table - Optimizing for menu loads
        Schema::table('categories', function (Blueprint $table) {
            if (!$this->indexExists('categories', 'idx_categories_vendor_active')) {
                $table->index(['vendor_id', 'is_available'], 'idx_categories_vendor_active');
            }

            if (!$this->indexExists('categories', 'idx_categories_reorder')) {
                $table->index(['reorder_id'], 'idx_categories_reorder');
            }
        });

        // Products table - For menu item searches
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!$this->indexExists('products', 'idx_products_category_vendor')) {
                    $table->index(['category_id', 'vendor_id'], 'idx_products_category_vendor');
                }

                if (!$this->indexExists('products', 'idx_products_vendor_active')) {
                    $table->index(['vendor_id', 'product_status'], 'idx_products_vendor_active');
                }
            });
        }

        // Users table - For vendor lookups
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'idx_users_type_active')) {
                $table->index(['type', 'is_available'], 'idx_users_type_active');
            }

            if (!$this->indexExists('users', 'idx_users_slug')) {
                $table->index(['slug'], 'idx_users_slug');
            }
        });

        // Carts table - For user sessions
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                if (!$this->indexExists('carts', 'idx_carts_user_vendor')) {
                    $table->index(['user_id', 'vendor_id'], 'idx_carts_user_vendor');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_vendor_status_created');
            $table->dropIndex('idx_orders_user_vendor');
            $table->dropIndex('idx_orders_status_created');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_vendor_active');
            $table->dropIndex('idx_categories_reorder');
        });

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_category_vendor');
                $table->dropIndex('idx_products_vendor_active');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_active');
            $table->dropIndex('idx_users_slug');
        });

        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropIndex('idx_carts_user_vendor');
            });
        }
    }

    /**
     * Check if index exists on table
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
            ->pluck('Key_name')
            ->toArray();

        return in_array($index, $indexes);
    }
}
