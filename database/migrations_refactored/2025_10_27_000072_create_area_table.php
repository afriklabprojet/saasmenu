<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: area
     * Original files: 2025_10_19_004113_create_area_table.php
     */
    public function up(): void
    {
        Schema::create('area', function (Blueprint $table) {
            $table->string('area');
            $table->foreign('city_id')->references('id')->on('city')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);
            $table->foreign('city_id')->references('id')->on('city')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area');
    }
};
