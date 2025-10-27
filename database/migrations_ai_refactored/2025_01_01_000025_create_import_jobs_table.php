<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: import_jobs
     * Purpose: Store import jobs data
     * Original migrations: 2024_01_01_000001_create_import_jobs_table.php
     */
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->enum('status')->default('pending');
            $table->string('file_path');
            $table->string('original_filename');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // Indexes
            $table->index(['restaurant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('scheduled_at');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};
