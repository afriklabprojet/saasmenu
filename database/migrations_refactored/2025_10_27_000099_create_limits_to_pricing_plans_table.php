<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: limits_to_pricing_plans
     * Original files: 2025_10_23_041541_add_limits_to_pricing_plans_table.php
     */
    public function up(): void
    {
        Schema::create('limits_to_pricing_plans', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limits_to_pricing_plans');
    }
};
