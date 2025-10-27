<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: blogs
     * Original files: 2022_10_22_051717_create_blogs_table.php
     */
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('title');
            $table->string('image');
            $table->longText('description');
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
