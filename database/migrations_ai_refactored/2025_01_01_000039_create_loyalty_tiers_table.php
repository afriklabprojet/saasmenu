<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: loyalty_tiers
     * Purpose: Store loyalty tiers data
     * Original migrations: 2024_01_15_000009_create_loyalty_tiers_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('min_points')->default(0);
            $table->integer('min_spent')->default(0);
            $table->integer('min_visits')->default(0);
            $table->decimal('points_multiplier')->default(1.00);
            $table->json('benefits')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index(['program_id', 'is_active']);
            $table->index('sort_order');

            // Foreign keys
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
