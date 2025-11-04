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
        // Add missing indexes identified in the audit report

        // Orders table - Critical for performance
        Schema::table('orders', function (Blueprint $table) {
            // Check if indexes don't exist before adding
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

        // Items table - For menu performance
        Schema::table('items', function (Blueprint $table) {
            if (!$this->indexExists('items', 'idx_items_vendor_available_reorder')) {
                $table->index(['vendor_id', 'is_available', 'reorder_id'], 'idx_items_vendor_available_reorder');
            }

            if (!$this->indexExists('items', 'idx_items_category_available')) {
                $table->index(['cat_id', 'is_available'], 'idx_items_category_available');
            }
        });

        // Categories table - For menu navigation
        Schema::table('categories', function (Blueprint $table) {
            if (!$this->indexExists('categories', 'idx_categories_vendor_available_reorder')) {
                $table->index(['vendor_id', 'is_available', 'reorder_id'], 'idx_categories_vendor_available_reorder');
            }
        });

        // Order details table - For order analysis
        Schema::table('order_details', function (Blueprint $table) {
            if (!$this->indexExists('order_details', 'idx_order_details_order_item')) {
                $table->index(['order_id', 'item_id'], 'idx_order_details_order_item');
            }

            if (!$this->indexExists('order_details', 'idx_order_details_item_created')) {
                $table->index(['item_id', 'created_at'], 'idx_order_details_item_created');
            }
        });

        // Carts table - For session management
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                if (!$this->indexExists('carts', 'idx_carts_user_vendor')) {
                    $table->index(['user_id', 'vendor_id'], 'idx_carts_user_vendor');
                }

                if (!$this->indexExists('carts', 'idx_carts_session_vendor')) {
                    $table->index(['session_id', 'vendor_id'], 'idx_carts_session_vendor');
                }
            });
        }

        // Users table - For authentication and vendor lookup
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'idx_users_vendor_type')) {
                $table->index(['vendor_id', 'type'], 'idx_users_vendor_type');
            }

            if (!$this->indexExists('users', 'idx_users_email_verified')) {
                $table->index(['email', 'email_verified_at'], 'idx_users_email_verified');
            }
        });

        // Customers table - For customer lookup
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!$this->indexExists('customers', 'idx_customers_vendor_active')) {
                    $table->index(['vendor_id', 'is_available'], 'idx_customers_vendor_active');
                }

                if (!$this->indexExists('customers', 'idx_customers_email_phone')) {
                    $table->index(['email', 'mobile'], 'idx_customers_email_phone');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop added indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_vendor_status_created');
            $table->dropIndex('idx_orders_customer_vendor');
            $table->dropIndex('idx_orders_status_created');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_vendor_available_reorder');
            $table->dropIndex('idx_items_category_available');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_vendor_available_reorder');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('idx_order_details_order_item');
            $table->dropIndex('idx_order_details_item_created');
        });

        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropIndex('idx_carts_user_vendor');
                $table->dropIndex('idx_carts_session_vendor');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_vendor_type');
            $table->dropIndex('idx_users_email_verified');
        });

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('idx_customers_vendor_active');
                $table->dropIndex('idx_customers_email_phone');
            });
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }
};
