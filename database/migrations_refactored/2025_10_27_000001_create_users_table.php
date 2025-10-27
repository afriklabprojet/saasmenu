<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: users
     * Original files: 2014_10_12_000000_create_users_table.php, 2022_11_10_000000_update_users_table_for_restro_saas.php
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('type')->default(1)->after('email');
            $table->string('slug')->nullable()->after('name');
            $table->string('mobile')->nullable()->after('email');
            $table->string('image')->nullable()->after('mobile');
            $table->integer('is_available')->default(1)->after('image');
            $table->integer('is_deleted')->default(2)->after('is_available');
            $table->integer('vendor_id')->nullable()->after('type');
            $table->dropColumn(['type', 'slug', 'mobile', 'image', 'is_available', 'is_deleted', 'vendor_id']);
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
