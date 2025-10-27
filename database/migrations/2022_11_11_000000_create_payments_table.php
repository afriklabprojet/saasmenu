<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('payment_name');
            $table->string('payment_type'); // 1=COD, 2=Razorpay, 3=Stripe, etc.
            $table->string('environment')->nullable(); // sandbox/live
            $table->text('public_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->string('currency')->nullable();
            $table->string('image')->nullable();
            $table->text('payment_description')->nullable(); // for bank transfer
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable(); 
            $table->string('bank_ifsc_code')->nullable();
            $table->string('encryption_key')->nullable(); // for flutterwave
            $table->string('base_url_by_region')->nullable(); // for paytab
            $table->integer('is_available')->default(1); // 1=yes, 2=no
            $table->integer('is_activate')->default(1); // 1=active, 2=inactive
            $table->integer('reorder_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}