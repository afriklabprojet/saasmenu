<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: tables
     * Original files: 2024_01_15_000002_create_tables_table.php
     */
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('table_number', 20);
            $table->string('name', 100)->nullable();
            $table->integer('capacity')->default(4);
            $table->string('location', 100)->nullable();
            $table->string('table_code', 20)->unique();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'occupied', 'free'])->default('active');
            $table->timestamp('last_accessed')->nullable();
            $table->unique(['restaurant_id', 'table_number']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'table_number']);
            $table->unique(['restaurant_id', 'table_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
