<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: testimonials
     * Original files: 2025_10_19_082914_create_testimonials_table.php
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->integer('star');
            $table->longText('description');
            $table->string('name');
            $table->string('image');
            $table->string('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
