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
     * Original migrations: 2022_12_10_033631_create_orders_table.php, 2024_01_15_000006_add_api_fields_to_orders_table.php
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_mobile');

            // Order details
            $table->enum('order_type', ['pickup', 'delivery', 'dining'])->default('pickup');
            $table->decimal('sub_total', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);

            // Status tracking
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'])
                  ->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();

            // API and integration fields
            $table->json('api_response')->nullable();
            $table->string('external_order_id')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->text('notes')->nullable();

            // Delivery address
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();

            // Table booking (for dining)
            $table->unsignedBigInteger('table_id')->nullable();
            $table->timestamp('booking_time')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['vendor_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('order_number');
            $table->index('payment_status');

            // Foreign keys
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
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
