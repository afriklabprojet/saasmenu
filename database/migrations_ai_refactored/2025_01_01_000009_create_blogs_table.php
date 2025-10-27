<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: blogs
     * Purpose: Store blog posts and articles
     * Original migrations: 2022_10_22_051717_create_blogs_table.php, 2025_10_18_204359_add_reorder_id_to_multiple_tables.php, 2025_10_18_211317_add_vendor_id_to_blogs_table.php, 2025_10_18_211317_add_vendor_id_to_blogs_table.php
     */
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title');
            $table->string('image');
            $table->longText('description');
            $table->integer('reorder_id')->default(0);
            $table->bigInteger('vendor_id')->default(1);

            // Indexes
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
