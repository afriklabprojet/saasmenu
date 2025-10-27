<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: banners
     * Original files: 2022_10_18_121106_create_banners_table.php, 2025_10_18_204359_add_reorder_id_to_multiple_tables.php
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('service_id')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);
            $table->string('image');
            $table->boolean('type')->nullable()->comment('1=category,2=service,3=')->default(null);
            $table->integer('section')->nullable()->comment('1=banner1,2=banner2,3=banner3')->default(1);
            $table->boolean('is_available')->comment('1=yes,2=no')->default('1');
            $table->integer('reorder_id')->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
