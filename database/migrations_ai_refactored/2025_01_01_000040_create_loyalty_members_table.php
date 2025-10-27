<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: loyalty_members
     * Purpose: Store customer loyalty program memberships
     * Original migrations: 2024_01_15_000010_create_loyalty_members_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_members', function (Blueprint $table) {
            $table->foreignId('restaurant_id');
            $table->foreignId('user_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->date('birth_date')->nullable();
            $table->string('member_code')->unique();
            $table->integer('points_balance')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->foreignId('tier_id')->nullable();
            $table->string('referral_code')->unique();
            $table->timestamp('joined_at');
            $table->timestamp('last_activity_at')->nullable();
            $table->enum('status')->default('active');
            $table->json('preferences')->nullable();

            // Indexes
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'email']);
            $table->index(['restaurant_id', 'phone']);
            $table->index(['restaurant_id', 'points_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_members');
    }
};
