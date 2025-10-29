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
     * Original migrations: 2014_10_12_000000_create_users_table.php, 2022_11_10_000000_update_users_table_for_restro_saas.php
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // RestroSaaS specific fields
            $table->integer('type')->default(1)->comment('1=admin, 2=vendor, 3=staff');
            $table->string('slug')->nullable()->unique();
            $table->string('mobile')->nullable();
            $table->string('image')->nullable();
            $table->integer('is_available')->default(1)->comment('1=available, 2=unavailable');
            $table->integer('is_deleted')->default(2)->comment('1=deleted, 2=active');
            $table->integer('vendor_id')->nullable();

            // Plan and subscription fields
            $table->unsignedBigInteger('plan_id')->nullable();

            // Location fields
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Social and features
            $table->boolean('available_on_landing')->default(true);
            $table->string('custom_domain')->nullable();
            $table->string('apple_id')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['type', 'is_deleted']);
            $table->index('vendor_id');
            $table->index('plan_id');
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
