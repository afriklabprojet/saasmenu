<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: systemaddons
     * Original files: 2025_10_18_195659_create_systemaddons_table.php
     */
    public function up(): void
    {
        Schema::create('systemaddons', function (Blueprint $table) {
            $table->string('name');
            $table->index('unique_identifier');
            $table->string('version', 20);
            $table->integer('activated');
            $table->string('image');
            $table->integer('type')->nullable();
            $table->index('unique_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systemaddons');
    }
};
