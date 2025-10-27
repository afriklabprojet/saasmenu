<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: loyalty_cards
     * Purpose: Store loyalty cards data
     * Original migrations: 2024_01_15_000004_create_loyalty_cards_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->foreignId('customer_id');
            $table->foreignId('restaurant_id');
            $table->foreignId('user_id');
            $table->string('card_number')->unique();
            $table->integer('points')->default(0);
            $table->decimal('total_spent')->default(0);
            $table->integer('visits_count')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamp('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_cards');
    }
};
