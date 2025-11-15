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
        if (Schema::hasTable('variants')) {
            return;
        }

        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->integer('qty')->default(0);
            $table->integer('min_order')->default(1);
            $table->integer('max_order')->default(0);
            $table->boolean('is_available')->default(1);
            $table->boolean('stock_management')->default(0);
            $table->integer('reorder_id')->default(0);
            $table->timestamps();

            $table->index('item_id');
            $table->index('reorder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
