<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: city
     * Original files: 2025_10_18_234135_create_city_table.php
     */
    public function up(): void
    {
        Schema::create('city', function (Blueprint $table) {
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->tinyInteger('is_available')->default(1);
            $table->tinyInteger('Is_deleted')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city');
    }
};
