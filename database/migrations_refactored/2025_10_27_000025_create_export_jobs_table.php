<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: export_jobs
     * Original files: 2024_01_01_000002_create_export_jobs_table.php
     */
    public function up(): void
    {
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('type');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('file_path')->nullable();
            $table->string('filename');
            $table->enum('format', ['csv', 'xlsx', 'json'])->default('csv');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->json('filters')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->index('scheduled_at');
            $table->integer('download_count')->default(0);
            $table->index('expires_at');
            $table->index(['restaurant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
            $table->index('expires_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_jobs');
    }
};
