<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: bookings
     * Original files: 2022_11_11_050805_create_bookings_table.php
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('booking_number');
            $table->integer('vendor_id');
            $table->integer('service_id');
            $table->string('service_image');
            $table->string('service_name');
            $table->string('offer_code');
            $table->double('offer_amount');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->longText('address');
            $table->integer('payment_status')->comment('1=Pending,2=for paid');
            $table->string('customer_name');
            $table->string('mobile');
            $table->string('email');
            $table->longText('message');
            $table->double('sub_total');
            $table->double('tax');
            $table->double('grand_total');
            $table->string('transaction_id');
            $table->string('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
