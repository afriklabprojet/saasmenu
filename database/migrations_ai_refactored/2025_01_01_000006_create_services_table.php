<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: services
     * Purpose: Store services data
     * Original migrations: 2022_09_29_104135_create_services_table.php
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('slug');
            $table->double('price');
            $table->string('description');
            $table->boolean('is_available')->default('1');
            $table->boolean('is_deleted')->default('2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
