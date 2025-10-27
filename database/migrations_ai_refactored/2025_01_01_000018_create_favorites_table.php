<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: favorites
     * Purpose: Store customer favorite items
     * Original migrations: 2022_12_02_034640_create_favorites_table.php
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
