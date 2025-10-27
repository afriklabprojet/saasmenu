<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: custom_domain_to_users
     * Original files: 2025_10_23_000000_add_custom_domain_to_users_table.php
     */
    public function up(): void
    {
        Schema::create('custom_domain_to_users', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_domain_to_users');
    }
};
