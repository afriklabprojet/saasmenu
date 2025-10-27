<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: wishlists
     * Original files: 2025_10_23_005100_create_wishlists_table.php
     */
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('item_id');
            $table->unique(['user_id', 'item_id']);
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
