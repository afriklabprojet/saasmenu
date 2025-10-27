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
        Schema::create('city', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la ville
            $table->string('code')->nullable(); // Code de la ville
            $table->text('description')->nullable(); // Description
            $table->integer('reorder_id')->default(0); // Ordre d'affichage
            $table->tinyInteger('is_available')->default(1); // 1=disponible, 0=indisponible
            $table->tinyInteger('Is_deleted')->default(2); // 1=supprimé, 2=actif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city');
    }
};
