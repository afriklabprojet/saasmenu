<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: area
     * Purpose: Store area data
     * Original migrations: 2025_10_19_004113_create_area_table.php
     */
    public function up(): void
    {
        Schema::create('area', function (Blueprint $table) {
            $table->string('area');
            $table->unsignedBigInteger('city_id');
            $table->string('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);

            // Foreign keys
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
