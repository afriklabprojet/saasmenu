<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: pos_carts
     * Original files: 2024_01_15_000021_create_pos_carts_table.php
     */
    public function up(): void
    {
        Schema::create('pos_carts', function (Blueprint $table) {
            $table->foreign('terminal_id')->references('id')->on('pos_terminals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('session_id');
            $table->index('menu_item_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->json('modifiers')->nullable();
            $table->text('special_instructions')->nullable();
            $table->index(['terminal_id', 'user_id']);
            $table->index(['terminal_id', 'user_id']);
            $table->index('session_id');
            $table->index('menu_item_id');
            $table->foreign('terminal_id')->references('id')->on('pos_terminals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('pos_sessions')->onDelete('cascade');
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_carts');
    }
};
