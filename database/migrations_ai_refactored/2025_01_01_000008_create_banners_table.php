<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: banners
     * Purpose: Store promotional banners and advertisements
     * Original migrations: 2022_10_18_121106_create_banners_table.php, 2025_10_18_204359_add_reorder_id_to_multiple_tables.php, 2025_10_18_205643_add_banner_image_to_banners_table.php, 2025_10_18_205643_add_banner_image_to_banners_table.php
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->integer('service_id')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);
            $table->string('image');
            $table->boolean('type')->nullable()->default(null)->comment('1=category,2=service,3=');
            $table->integer('section')->nullable()->default(1)->comment('1=banner1,2=banner2,3=banner3');
            $table->boolean('is_available')->default('1')->comment('1=yes,2=no');
            $table->integer('reorder_id')->default(0);
            $table->string('banner_image')->nullable();
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
