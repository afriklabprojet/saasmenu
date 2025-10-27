<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: languages
     * Purpose: Store multi-language support configurations
     * Original migrations: 2025_10_18_195300_create_languages_table.php, 2025_10_25_043713_create_languages_table.php, 2025_10_25_044118_add_multilanguage_fields_to_languages_table.php, 2025_10_25_044118_add_multilanguage_fields_to_languages_table.php
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->string('name');
            $table->string('code');
            $table->string('layout')->default('ltr');
            $table->string('image')->nullable();
            $table->enum('is_default')->default(2);
            $table->enum('is_available')->default(1);
            $table->enum('is_deleted')->default(2);

            // Indexes
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
