<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: table_ratings
     * Purpose: Store table ratings data
     * Original migrations: 2024_01_15_000003_create_table_ratings_table.php
     */
    public function up(): void
    {
        Schema::create('table_ratings', function (Blueprint $table) {
            $table->foreignId('restaurant_id');
            $table->foreignId('table_id');
            $table->unsignedInteger('order_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // Indexes
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['table_id', 'created_at']);
            $table->index(['rating']);

            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_ratings');
    }
};
