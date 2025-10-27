<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: users
     * Purpose: Store user accounts (admins, restaurant owners, staff)
     * Original migrations: 2014_10_12_000000_create_users_table.php, 2022_11_10_000000_update_users_table_for_restro_saas.php, 2022_11_10_000000_update_users_table_for_restro_saas.php, 2024_01_01_000000_create_all_tables.php, 2025_10_18_201517_add_plan_id_to_users_table.php, 2025_10_18_201517_add_plan_id_to_users_table.php, 2025_10_19_083522_add_available_on_landing_to_users_table.php, 2025_10_19_083522_add_available_on_landing_to_users_table.php, 2025_10_19_090041_add_location_columns_to_users_table.php, 2025_10_19_090041_add_location_columns_to_users_table.php, 2025_10_23_000000_add_custom_domain_to_users_table.php, 2025_10_23_000000_add_custom_domain_to_users_table.php, 2025_10_25_110707_add_apple_id_to_users_table.php, 2025_10_25_110707_add_apple_id_to_users_table.php
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('type')->default(1);
            $table->string('slug')->nullable();
            $table->string('mobile')->nullable();
            $table->string('image')->nullable();
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);
            $table->integer('vendor_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->boolean('allow_without_subscription')->default(1);
            $table->integer('available_on_landing')->default(2)->comment('1 = Yes, 2 = No');
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('login_type')->default('manual');
            $table->text('description')->nullable();
            $table->longText('token')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->string('purchase_amount')->nullable();
            $table->string('purchase_date')->nullable();
            $table->string('payment_id')->nullable();
            $table->integer('payment_type')->nullable();
            $table->integer('free_plan')->default(0);
            $table->tinyInteger('is_delivery')->nullable()->comment('1=Yes,2=No');
            $table->tinyInteger('is_verified')->default(2)->comment('1=Yes,2=No');
            $table->text('license_type')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('store_id')->nullable();
            $table->string('custom_domain')->nullable();
            $table->boolean('domain_verified')->default(false);
            $table->timestamp('domain_verified_at')->nullable();
            $table->string('apple_id')->nullable();

            // Indexes
            $table->index(['city_id', 'area_id', 'store_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
