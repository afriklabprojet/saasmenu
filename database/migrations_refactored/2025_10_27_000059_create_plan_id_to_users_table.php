<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: plan_id_to_users
     * Original files: 2025_10_18_201517_add_plan_id_to_users_table.php
     */
    public function up(): void
    {
        Schema::create('plan_id_to_users', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_id_to_users');
    }
};
