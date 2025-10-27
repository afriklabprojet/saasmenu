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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la langue
            $table->string('code', 5); // Code de la langue (en, fr, etc.)
            $table->string('layout', 10)->default('ltr'); // Direction (ltr, rtl)
            $table->string('image')->nullable(); // Image/flag de la langue
            $table->enum('is_default', [1, 2])->default(2); // 1 = default, 2 = not default
            $table->enum('is_available', [1, 2])->default(1); // 1 = available, 2 = not available
            $table->enum('is_deleted', [1, 2])->default(2); // 1 = deleted, 2 = not deleted
            $table->timestamps();

            // Index sur le code pour les recherches fréquentes
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
