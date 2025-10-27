<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: import_export_jobs
     * Purpose: Store import export jobs data
     * Original migrations: 2024_01_02_000000_create_import_export_tables.php
     */
    public function up(): void
    {
        Schema::create('import_export_jobs', function (Blueprint $table) {
            $table->enum('type');
            $table->string('data_type');
            $table->string('file_path')->nullable();
            $table->string('export_file_path')->nullable();
            $table->enum('status')->default('pending');
            $table->string('format')->nullable();
            $table->json('mapping')->nullable();
            $table->json('filters')->nullable();
            $table->json('options')->nullable();
            $table->foreignId('user_id');
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('errors')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('progress')->default(0);
            $table->timestamp('estimated_completion')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('memory_usage')->nullable();
            $table->integer('execution_time')->nullable();
            $table->json('metadata')->nullable();
            $table->index('created_at');
            $table->timestamps();

            // Indexes
            $table->index(['type', 'status']);
            $table->index(['data_type', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_export_jobs');
    }
};
