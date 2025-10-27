<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: loyalty_tiers
     * Original files: 2024_01_15_000009_create_loyalty_tiers_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->foreign('program_id')->references('id')->on('loyalty_programs')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('min_points')->default(0);
            $table->integer('min_spent')->default(0);
            $table->integer('min_visits')->default(0);
            $table->decimal('points_multiplier', 3, 2)->default(1.00);
            $table->json('benefits')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->index('sort_order');
            $table->boolean('is_active')->default(true);
            $table->index(['program_id', 'is_active']);
            $table->index(['program_id', 'is_active']);
            $table->index('sort_order');
            $table->foreign('program_id')->references('id')->on('loyalty_programs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
