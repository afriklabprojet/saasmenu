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
        Schema::table('users', function (Blueprint $table) {
            // Add authentication columns first
            $table->string('google_id')->nullable()->after('password');
            $table->string('facebook_id')->nullable()->after('google_id');
            $table->string('login_type')->default('manual')->after('facebook_id');

            // Add business columns
            $table->text('description')->nullable()->after('type');
            $table->longText('token')->nullable()->after('description');

            // Add location columns
            $table->integer('city_id')->nullable()->after('token');
            $table->integer('area_id')->nullable()->after('city_id');

            // Add payment columns
            $table->string('purchase_amount')->nullable()->after('plan_id');
            $table->string('purchase_date')->nullable()->after('purchase_amount');
            $table->string('payment_id')->nullable()->after('available_on_landing');
            $table->integer('payment_type')->nullable()->after('payment_id');
            $table->integer('free_plan')->default(0)->after('payment_type');
            $table->tinyInteger('is_delivery')->nullable()->comment('1=Yes,2=No')->after('free_plan');
            $table->tinyInteger('is_verified')->default(2)->comment('1=Yes,2=No')->after('allow_without_subscription');

            // Add additional columns
            $table->text('license_type')->nullable()->after('remember_token');
            $table->integer('role_id')->nullable()->after('is_deleted');
            $table->integer('store_id')->nullable()->after('role_id');

            // Add indexes for better performance
            $table->index(['city_id', 'area_id', 'store_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn([
                'city_id', 'area_id', 'store_id', 'google_id', 'facebook_id',
                'login_type', 'description', 'token', 'purchase_amount',
                'purchase_date', 'payment_id', 'payment_type', 'free_plan',
                'is_delivery', 'is_verified', 'license_type', 'role_id'
            ]);
        });
    }
};
