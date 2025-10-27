<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: restaurants
     * Original files: 2024_01_15_000000_create_restaurants_table.php
     */
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('restaurant_name');
            $table->string('restaurant_slug')->unique();
            $table->text('restaurant_address');
            $table->string('restaurant_phone', 20)->nullable();
            $table->string('restaurant_email')->nullable();
            $table->string('restaurant_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(1);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('minimum_order', 8, 2)->default(0);
            $table->integer('delivery_time')->default(30);
            $table->time('opening_time')->default('09:00');
            $table->time('closing_time')->default('22:00');
            $table->boolean('is_open')->default(1);
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('total_reviews')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
