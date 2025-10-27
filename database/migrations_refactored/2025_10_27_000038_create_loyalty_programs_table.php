<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: loyalty_programs
     * Original files: 2024_01_15_000008_create_loyalty_programs_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('type');
            $table->decimal('points_per_currency', 8, 2)->default(1.00);
            $table->decimal('currency_per_point', 8, 2)->default(0.01);
            $table->integer('min_points_redemption')->default(100);
            $table->integer('points_expiry_months')->nullable();
            $table->json('tiers')->nullable();
            $table->json('rules')->nullable();
            $table->json('settings')->nullable();
            $table->index(['restaurant_id', 'is_active']);
            $table->index(['restaurant_id', 'is_active']);
            $table->index('type');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_programs');
    }
};
