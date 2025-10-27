<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: loyalty_redemptions
     * Purpose: Store loyalty reward redemption records
     * Original migrations: 2024_01_15_000013_create_loyalty_redemptions_table.php, 2024_01_15_000014_create_loyalty_redemptions_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->foreignId('member_id');
            $table->foreignId('restaurant_id');
            $table->foreignId('reward_id');
            $table->unsignedInteger('order_id')->nullable();
            $table->integer('points_used');
            $table->decimal('discount_amount')->nullable();
            $table->string('redeem_code')->unique();
            $table->enum('status')->default('pending');
            $table->timestamp('redeemed_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('used_by')->nullable();
            $table->text('notes')->nullable();

            // Indexes
            $table->index(['member_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['reward_id']);
            $table->index(['redeem_code']);
            $table->index(['expires_at']);
            $table->index(['member_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['reward_id']);
            $table->index(['redeem_code']);
            $table->index(['expires_at']);

            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemptions');
    }
};
