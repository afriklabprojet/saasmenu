<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'stock_management')) {
                $table->boolean('stock_management')->default(0)->after('is_available');
            }
            if (!Schema::hasColumn('items', 'qty')) {
                $table->integer('qty')->default(0)->after('stock_management');
            }
            if (!Schema::hasColumn('items', 'min_order')) {
                $table->integer('min_order')->default(1)->after('qty');
            }
            if (!Schema::hasColumn('items', 'max_order')) {
                $table->integer('max_order')->default(0)->after('min_order');
            }
            if (!Schema::hasColumn('items', 'low_qty')) {
                $table->integer('low_qty')->default(0)->after('max_order');
            }
            if (!Schema::hasColumn('items', 'tax')) {
                $table->string('tax', 100)->nullable()->after('low_qty');
            }
            if (!Schema::hasColumn('items', 'sku')) {
                $table->string('sku', 100)->nullable()->after('tax');
            }
            if (!Schema::hasColumn('items', 'reorder_id')) {
                $table->integer('reorder_id')->default(0)->after('sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'stock_management',
                'qty',
                'min_order',
                'max_order',
                'low_qty',
                'tax',
                'sku',
                'reorder_id'
            ]);
        });
    }
};
