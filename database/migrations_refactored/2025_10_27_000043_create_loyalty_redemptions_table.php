<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: loyalty_redemptions
     * Original files: 2024_01_15_000013_create_loyalty_redemptions_table.php, 2024_01_15_000014_create_loyalty_redemptions_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('loyalty_rewards')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->integer('points_used');
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->string('redeem_code', 20)->unique();
            $table->enum('status', ['pending', 'used', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('redeemed_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
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
            $table->index(['member_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['reward_id']);
            $table->index(['redeem_code']);
            $table->index(['expires_at']);
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
