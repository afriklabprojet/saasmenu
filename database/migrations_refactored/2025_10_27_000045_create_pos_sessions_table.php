<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: pos_sessions
     * Original files: 2024_01_15_000015_create_pos_sessions_table.php
     */
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('terminal_id')->references('id')->on('pos_terminals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('status', ['active', 'closed', 'suspended'])->default('active');
            $table->decimal('opening_cash', 10, 2)->default(0);
            $table->decimal('closing_cash', 10, 2)->nullable();
            $table->decimal('expected_cash', 10, 2)->nullable();
            $table->decimal('cash_difference', 10, 2)->nullable();
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->json('payment_summary')->nullable();
            $table->index('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->index(['restaurant_id', 'status']);
            $table->index(['terminal_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['terminal_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('started_at');
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
