<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: apple_id_to_users
     * Original files: 2025_10_25_110707_add_apple_id_to_users_table.php
     */
    public function up(): void
    {
        Schema::create('apple_id_to_users', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apple_id_to_users');
    }
};
