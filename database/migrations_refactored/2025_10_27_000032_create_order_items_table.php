<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: order_items
     * Original files: 2024_01_15_000003_create_order_items_table.php
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->string('item_name');
            $table->json('item_options')->nullable();
            $table->text('special_instructions')->nullable();
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
