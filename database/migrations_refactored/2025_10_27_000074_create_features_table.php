<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: features
     * Original files: 2025_10_19_082329_create_features_table.php
     */
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->string('title');
            $table->longText('description');
            $table->string('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
