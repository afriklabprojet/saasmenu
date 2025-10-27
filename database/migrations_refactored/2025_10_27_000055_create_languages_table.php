<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: languages
     * Original files: 2025_10_18_195300_create_languages_table.php, 2025_10_25_043713_create_languages_table.php
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->string('name');
            $table->index('code');
            $table->string('layout', 10)->default('ltr');
            $table->string('image')->nullable();
            $table->enum('is_default', [1, 2])->default(2);
            $table->enum('is_available', [1, 2])->default(1);
            $table->enum('is_deleted', [1, 2])->default(2);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
