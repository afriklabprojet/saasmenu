<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: service_images
     * Original files: 2022_09_29_110444_create_service_images_table.php
     */
    public function up(): void
    {
        Schema::create('service_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->string('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_images');
    }
};
