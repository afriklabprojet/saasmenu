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
        Schema::table('carts', function (Blueprint $table) {
            // Add the new item columns that the code expects
            $table->integer('item_id')->nullable()->after('session_id');
            $table->string('item_name')->nullable()->after('item_id');
            $table->string('item_image')->nullable()->after('item_name');
            $table->double('item_price')->nullable()->after('item_image');

            // Add extras columns
            $table->integer('extras_id')->nullable()->after('item_price');
            $table->string('extras_name')->nullable()->after('extras_id');
            $table->double('extras_price')->nullable()->after('extras_name');

            // Add price and tax columns (different from product_price/product_tax)
            $table->double('price')->nullable()->after('qty');
            $table->double('tax')->nullable()->after('price');

            // Add variants columns
            $table->integer('variants_id')->nullable()->after('tax');
            $table->string('variants_name')->nullable()->after('variants_id');
            $table->double('variants_price')->nullable()->after('variants_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn([
                'item_id',
                'item_name',
                'item_image',
                'item_price',
                'extras_id',
                'extras_name',
                'extras_price',
                'price',
                'tax',
                'variants_id',
                'variants_name',
                'variants_price'
            ]);
        });
    }
};
