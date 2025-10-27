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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Client
            $table->unsignedBigInteger('item_id'); // Produit
            $table->timestamps();

            // Index et contraintes d'unicité
            $table->index('user_id');
            $table->index('item_id');
            $table->unique(['user_id', 'item_id']); // Un produit une seule fois par utilisateur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
