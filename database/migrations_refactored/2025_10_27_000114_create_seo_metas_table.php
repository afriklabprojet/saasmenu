<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: seo_metas
     * Original files: 2025_10_25_114415_create_seo_metas_table.php, 2025_10_25_114427_create_seo_metas_table.php
     */
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->string('page')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
