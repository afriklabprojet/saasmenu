<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: loyalty_transactions
     * Original files: 2024_01_15_000011_create_loyalty_transactions_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->integer('points');
            $table->integer('balance_after');
            $table->text('description');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('metadata')->nullable();
            $table->index(['member_id', 'created_at']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['type']);
            $table->index(['expires_at']);
            $table->index(['member_id', 'created_at']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['type']);
            $table->index(['expires_at']);
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
