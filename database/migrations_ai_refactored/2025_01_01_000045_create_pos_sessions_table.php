<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: pos_sessions
     * Purpose: Store POS login sessions and shifts
     * Original migrations: 2024_01_15_000015_create_pos_sessions_table.php
     */
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('terminal_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status')->default('active');
            $table->decimal('opening_cash')->default(0);
            $table->decimal('closing_cash')->nullable();
            $table->decimal('expected_cash')->nullable();
            $table->decimal('cash_difference')->nullable();
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales')->default(0);
            $table->json('payment_summary')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();

            // Indexes
            $table->index(['restaurant_id', 'status']);
            $table->index(['terminal_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('started_at');

            // Foreign keys
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('terminal_id')->references('id')->on('pos_terminals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
