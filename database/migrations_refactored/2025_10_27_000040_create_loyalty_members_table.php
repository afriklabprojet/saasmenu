<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: loyalty_members
     * Original files: 2024_01_15_000010_create_loyalty_members_table.php
     */
    public function up(): void
    {
        Schema::create('loyalty_members', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->date('birth_date')->nullable();
            $table->string('member_code', 20)->unique();
            $table->integer('points_balance')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->onDelete('set null');
            $table->string('referral_code', 20)->unique();
            $table->timestamp('joined_at');
            $table->timestamp('last_activity_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('preferences')->nullable();
            $table->unique(['restaurant_id', 'phone']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'email']);
            $table->index(['restaurant_id', 'phone']);
            $table->index(['restaurant_id', 'points_balance']);
            $table->unique(['restaurant_id', 'email']);
            $table->unique(['restaurant_id', 'phone']);
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
