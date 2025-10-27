<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: table_ratings
     * Original files: 2024_01_15_000003_create_table_ratings_table.php
     */
    public function up(): void
    {
        Schema::create('table_ratings', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->integer('rating')->between(1, 5);
            $table->text('comment')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['table_id', 'created_at']);
            $table->index(['rating']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['table_id', 'created_at']);
            $table->index(['rating']);
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
