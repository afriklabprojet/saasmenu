<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: pos_terminals
     * Original files: 2024_01_15_000014_create_pos_terminals_table.php
     */
    public function up(): void
    {
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('location')->nullable();
            $table->index('current_user_id');
            $table->index('last_activity');
            $table->json('settings')->nullable();
            $table->json('hardware_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index('current_user_id');
            $table->index('last_activity');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('current_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_terminals');
    }
};
