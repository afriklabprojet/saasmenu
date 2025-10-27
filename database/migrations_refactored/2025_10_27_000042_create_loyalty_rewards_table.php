<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: loyalty_rewards
     * Original files: 2024_01_15_000012_create_loyalty_rewards_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->onDelete('set null');
            $table->string('title', 200);
            $table->text('description');
            $table->decimal('reward_value', 10, 2);
            $table->integer('points_required');
            $table->string('image_url')->nullable();
            $table->json('terms_conditions')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_member')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->index(['restaurant_id', 'tier_id']);
            $table->index(['points_required']);
            $table->index(['valid_from', 'valid_until']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'tier_id']);
            $table->index(['points_required']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
    }
};
