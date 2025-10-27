<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: seo_meta
     * Original files: 2025_10_25_043341_create_seo_meta_table.php
     */
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('page_type', 50);
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_card', 50)->default('summary_large_image');
            $table->text('schema_markup')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('index')->default(true);
            $table->boolean('follow')->default(true);
            $table->index(['vendor_id', 'page_type', 'page_id']);
            $table->index(['vendor_id', 'page_type', 'page_id']);
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
