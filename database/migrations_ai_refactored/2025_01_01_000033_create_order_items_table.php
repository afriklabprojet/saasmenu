<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: order_items
     * Purpose: Store order line items with quantities and prices
     * Original migrations: 2024_01_15_000003_create_order_items_table.php
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->foreignId('item_id');
            $table->integer('quantity');
            $table->decimal('price');
            $table->decimal('subtotal');
            $table->string('item_name');
            $table->json('item_options')->nullable();
            $table->text('special_instructions')->nullable();

            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
