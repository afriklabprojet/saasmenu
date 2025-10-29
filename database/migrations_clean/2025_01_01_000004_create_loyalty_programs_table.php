<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Table: loyalty_programs
     * Purpose: Store loyalty program configurations for restaurants
     * Original migrations: 2024_01_15_000008_create_loyalty_programs_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('vendor_id');

            // Program settings
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['points', 'visits', 'amount'])->default('points');
            $table->decimal('earn_rate', 5, 2)->default(1.00); // Points per dollar or visit
            $table->decimal('redeem_rate', 5, 2)->default(0.01); // Dollar value per point

            // Limits and rules
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->integer('max_points_per_order')->nullable();
            $table->integer('max_redemption_per_order')->nullable();
            $table->integer('points_expiry_days')->nullable();

            // Rewards and bonuses
            $table->integer('signup_bonus')->default(0);
            $table->integer('birthday_bonus')->default(0);
            $table->integer('referral_bonus')->default(0);

            // Tiers
            $table->boolean('has_tiers')->default(false);
            $table->json('tier_config')->nullable(); // Store tier configurations

            $table->timestamps();

            // Indexes
            $table->index(['vendor_id', 'is_active']);
            $table->index('type');

            // Foreign keys
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
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
