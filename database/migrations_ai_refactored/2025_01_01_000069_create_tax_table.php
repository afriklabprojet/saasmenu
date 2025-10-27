<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: tax
     * Purpose: Store tax data
     * Original migrations: 2025_10_19_092602_create_tax_table.php
     */
    public function up(): void
    {
        Schema::create('tax', function (Blueprint $table) {
            $table->bigInteger('vendor_id');
            $table->string('name');
            $table->decimal('percentage')->default(0);
            $table->text('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->tinyInteger('is_available')->default(1)->comment('1=Yes, 2=No');
            $table->tinyInteger('is_deleted')->default(2)->comment('1=Yes, 2=No');

            // Indexes
            $table->index(['vendor_id', 'is_deleted', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax');
    }
};
