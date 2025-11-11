<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSafePerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orders table - Critical for performance - Check columns exist first
        if (Schema::hasTable('orders')) {
            $orderColumns = Schema::getColumnListing('orders');

            Schema::table('orders', function (Blueprint $table) use ($orderColumns) {
                // Index for vendor + status + created queries
                if (in_array('vendor_id', $orderColumns) && in_array('status', $orderColumns) && in_array('created_at', $orderColumns)) {
                    if (!$this->indexExists('orders', 'idx_orders_vendor_status_created')) {
                        $table->index(['vendor_id', 'status', 'created_at'], 'idx_orders_vendor_status_created');
                    }
                }

                // Index for user + vendor queries
                if (in_array('user_id', $orderColumns) && in_array('vendor_id', $orderColumns)) {
                    if (!$this->indexExists('orders', 'idx_orders_user_vendor')) {
                        $table->index(['user_id', 'vendor_id'], 'idx_orders_user_vendor');
                    }
                }

                // Index for status + created queries
                if (in_array('status', $orderColumns) && in_array('created_at', $orderColumns)) {
                    if (!$this->indexExists('orders', 'idx_orders_status_created')) {
                        $table->index(['status', 'created_at'], 'idx_orders_status_created');
                    }
                }
            });
        }

        // Categories table - Optimizing for menu loads
        if (Schema::hasTable('categories')) {
            $categoryColumns = Schema::getColumnListing('categories');

            Schema::table('categories', function (Blueprint $table) use ($categoryColumns) {
                // Index for vendor + active status
                if (in_array('vendor_id', $categoryColumns) && in_array('is_available', $categoryColumns)) {
                    if (!$this->indexExists('categories', 'idx_categories_vendor_active')) {
                        $table->index(['vendor_id', 'is_available'], 'idx_categories_vendor_active');
                    }
                }

                // Only add reorder index if column exists
                if (in_array('reorder_id', $categoryColumns)) {
                    if (!$this->indexExists('categories', 'idx_categories_reorder')) {
                        $table->index(['reorder_id'], 'idx_categories_reorder');
                    }
                }
            });
        }

        // Users table - For vendor lookups
        if (Schema::hasTable('users')) {
            $userColumns = Schema::getColumnListing('users');

            Schema::table('users', function (Blueprint $table) use ($userColumns) {
                // Index for type + active status
                if (in_array('type', $userColumns) && in_array('is_available', $userColumns)) {
                    if (!$this->indexExists('users', 'idx_users_type_active')) {
                        $table->index(['type', 'is_available'], 'idx_users_type_active');
                    }
                }

                // Index for slug lookups
                if (in_array('slug', $userColumns)) {
                    if (!$this->indexExists('users', 'idx_users_slug')) {
                        $table->index(['slug'], 'idx_users_slug');
                    }
                }
            });
        }

        // Products table - For menu item searches
        if (Schema::hasTable('products')) {
            $productColumns = Schema::getColumnListing('products');

            Schema::table('products', function (Blueprint $table) use ($productColumns) {
                // Index for category + vendor queries
                if (in_array('category_id', $productColumns) && in_array('vendor_id', $productColumns)) {
                    if (!$this->indexExists('products', 'idx_products_category_vendor')) {
                        $table->index(['category_id', 'vendor_id'], 'idx_products_category_vendor');
                    }
                }

                // Index for vendor + status queries
                if (in_array('vendor_id', $productColumns) && in_array('product_status', $productColumns)) {
                    if (!$this->indexExists('products', 'idx_products_vendor_active')) {
                        $table->index(['vendor_id', 'product_status'], 'idx_products_vendor_active');
                    }
                }
            });
        }

        // Carts table - For user sessions
        if (Schema::hasTable('carts')) {
            $cartColumns = Schema::getColumnListing('carts');

            Schema::table('carts', function (Blueprint $table) use ($cartColumns) {
                // Index for user + vendor queries
                if (in_array('user_id', $cartColumns) && in_array('vendor_id', $cartColumns)) {
                    if (!$this->indexExists('carts', 'idx_carts_user_vendor')) {
                        $table->index(['user_id', 'vendor_id'], 'idx_carts_user_vendor');
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes if they exist
        $this->dropIndexSafe('orders', 'idx_orders_vendor_status_created');
        $this->dropIndexSafe('orders', 'idx_orders_user_vendor');
        $this->dropIndexSafe('orders', 'idx_orders_status_created');

        $this->dropIndexSafe('categories', 'idx_categories_vendor_active');
        $this->dropIndexSafe('categories', 'idx_categories_reorder');

        $this->dropIndexSafe('products', 'idx_products_category_vendor');
        $this->dropIndexSafe('products', 'idx_products_vendor_active');

        $this->dropIndexSafe('users', 'idx_users_type_active');
        $this->dropIndexSafe('users', 'idx_users_slug');

        $this->dropIndexSafe('carts', 'idx_carts_user_vendor');
    }

    /**
     * Check if index exists on table
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')
                ->toArray();

            return in_array($index, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Safely drop index if it exists
     */
    private function dropIndexSafe(string $table, string $index): void
    {
        if (Schema::hasTable($table) && $this->indexExists($table, $index)) {
            Schema::table($table, function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        }
    }
}
