<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: seo_meta
     * Purpose: Store seo meta data
     * Original migrations: 2025_10_25_043341_create_seo_meta_table.php
     */
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id');
            $table->string('page_type');
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_card')->default('summary_large_image');
            $table->text('schema_markup')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('index')->default(true);
            $table->boolean('follow')->default(true);

            // Indexes
            $table->index(['vendor_id', 'page_type', 'page_id']);

            // Foreign keys
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
