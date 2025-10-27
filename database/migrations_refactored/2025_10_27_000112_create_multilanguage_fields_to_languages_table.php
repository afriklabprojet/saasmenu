<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: multilanguage_fields_to_languages
     * Original files: 2025_10_25_044118_add_multilanguage_fields_to_languages_table.php
     */
    public function up(): void
    {
        Schema::create('multilanguage_fields_to_languages', function (Blueprint $table) {
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multilanguage_fields_to_languages');
    }
};
