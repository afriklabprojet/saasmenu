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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'restaurant_id')) {
                $table->foreignId('restaurant_id')->nullable()->after('customer_id')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending')->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 8, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 8, 2)->default(0)->after('delivery_fee');
            }
            if (!Schema::hasColumn('orders', 'delivery_type')) {
                $table->enum('delivery_type', ['delivery', 'pickup'])->default('delivery')->after('tax');
            }
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('delivery_type');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('delivery_address');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'special_instructions')) {
                $table->text('special_instructions')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'estimated_delivery_time')) {
                $table->timestamp('estimated_delivery_time')->nullable()->after('special_instructions');
            }
            if (!Schema::hasColumn('orders', 'rating')) {
                $table->tinyInteger('rating')->nullable()->after('estimated_delivery_time');
            }
            if (!Schema::hasColumn('orders', 'review')) {
                $table->text('review')->nullable()->after('rating');
            }
            if (!Schema::hasColumn('orders', 'rated_at')) {
                $table->timestamp('rated_at')->nullable()->after('review');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('rated_at');
            }
            if (!Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_id', 'restaurant_id', 'order_number', 'status', 'subtotal',
                'delivery_fee', 'tax', 'delivery_type', 'delivery_address',
                'payment_method', 'payment_status', 'special_instructions',
                'estimated_delivery_time', 'rating', 'review', 'rated_at',
                'cancelled_at', 'cancellation_reason'
            ]);
        });
    }
};
