<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: orders
     * Purpose: Store customer orders from restaurants
     * Original migrations: 2022_12_10_033631_create_orders_table.php, 2024_01_15_000006_add_api_fields_to_orders_table.php, 2024_01_15_000006_add_api_fields_to_orders_table.php, 2025_10_23_103000_add_status_columns_to_orders_table.php, 2025_10_23_103000_add_status_columns_to_orders_table.php, 2025_10_23_104500_add_order_details_to_orders_table.php, 2025_10_23_104500_add_order_details_to_orders_table.php
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->integer('user_id')->nullable();
            $table->text('session_id')->nullable();
            $table->string('order_number');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('user_mobile');
            $table->string('billing_address');
            $table->string('billing_landmark');
            $table->string('billing_postal_code');
            $table->string('billing_city');
            $table->string('billing_state');
            $table->string('billing_country');
            $table->string('shipping_address');
            $table->string('shipping_landmark');
            $table->string('shipping_postal_code');
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_country');
            $table->double('sub_total')->default(0.0);
            $table->string('offer_code')->nullable();
            $table->double('offer_amount')->nullable()->default(0.0);
            $table->double('tax_amount')->default(0.0);
            $table->string('shipping_area');
            $table->double('delivery_charge')->default(0.0);
            $table->double('grand_total')->default(0.0);
            $table->string('transaction_id')->nullable();
            $table->boolean('transaction_type')->default(1);
            $table->integer('status')->comment('1 = order placed , 2 = order confirmed/accepted , 3 = order cancelled/rejected - by admin , 4 = order cancelled/rejected - by user/customer , 5 = order delivered , ');
            $table->longText('notes')->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('restaurant_id')->nullable();
            $table->decimal('subtotal')->default(0);
            $table->decimal('delivery_fee')->default(0);
            $table->decimal('tax')->default(0);
            $table->enum('delivery_type')->default('delivery');
            $table->text('delivery_address')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->text('special_instructions')->nullable();
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->timestamp('rated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->tinyInteger('status_type')->default(1)->comment('1=pending, 2=processing, 3=completed, 4=cancelled');
            $table->tinyInteger('order_type')->default(1);
            $table->string('payment_type')->nullable();
            $table->string('payment_id')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
