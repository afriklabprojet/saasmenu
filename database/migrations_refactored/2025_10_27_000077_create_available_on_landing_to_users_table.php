<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: available_on_landing_to_users
     * Original files: 2025_10_19_083522_add_available_on_landing_to_users_table.php
     */
    public function up(): void
    {
        Schema::create('available_on_landing_to_users', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_on_landing_to_users');
    }
};
