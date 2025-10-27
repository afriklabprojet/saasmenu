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
        Schema::create('table_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->integer('total_members')->default(1);
            $table->date('booking_date');
            $table->time('booking_time');
            $table->text('message')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=confirmed, 3=cancelled');
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('status');
            $table->index('booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_book');
    }
};
