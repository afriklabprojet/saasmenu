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
        Schema::create('tax', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vendor_id');
            $table->string('name');
            $table->decimal('percentage', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->tinyInteger('is_available')->default(1)->comment('1=Yes, 2=No');
            $table->tinyInteger('is_deleted')->default(2)->comment('1=Yes, 2=No');
            $table->timestamps();

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
