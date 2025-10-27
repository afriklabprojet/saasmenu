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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Client
            $table->string('address_name', 100); // Maison, Bureau, etc.
            $table->text('address'); // Adresse complète
            $table->string('phone', 20); // Téléphone de contact
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Index
            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
