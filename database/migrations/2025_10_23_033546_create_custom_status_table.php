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
        Schema::create('custom_status', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->default(0);
            $table->integer('vendor_id');
            $table->string('name');
            $table->integer('type')->comment('1=default,2=process,3=complete,4=cancel');
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);
            $table->integer('order_type')->default(1)->comment('1=delivery,2=pickup,3=dinein,4=pos');
            $table->timestamps();

            $table->index('vendor_id');
            $table->index(['vendor_id', 'order_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_status');
    }
};
