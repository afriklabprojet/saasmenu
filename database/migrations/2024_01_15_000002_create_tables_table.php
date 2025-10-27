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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('table_number', 20);
            $table->string('name', 100)->nullable();
            $table->integer('capacity')->default(4);
            $table->string('location', 100)->nullable();
            $table->string('table_code', 20)->unique();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'occupied', 'free'])->default('active');
            $table->timestamp('last_accessed')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les requêtes
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