<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // menus, customers, orders, categories
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
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade'); // Commented out until restaurants table exists
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['restaurant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
            $table->index('expires_at');
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
