<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactored migration for table: settings
     * Original files: 2022_11_15_000000_create_settings_table.php
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->string('currency', 20)->default('XOF');
            $table->enum('currency_position', ['left', 'right'])->default('left');
            $table->boolean('currency_space')->default(1);
            $table->integer('decimal_separator')->default(1);
            $table->integer('currency_formate')->default(2);
            $table->boolean('maintenance_mode')->default(0);
            $table->boolean('checkout_login_required')->default(0);
            $table->boolean('is_checkout_login_required')->default(0);
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('delivery_type')->default('1,2');
            $table->string('timezone')->default('UTC');
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('contact')->nullable();
            $table->text('copyright')->nullable();
            $table->string('website_title')->default('RestroSaaS');
            $table->string('meta_title')->default('RestroSaaS - Restaurant Management System');
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('template')->default('default');
            $table->integer('template_type')->default(1);
            $table->string('primary_color')->default('#181D31');
            $table->string('secondary_color')->default('#6096B4');
            $table->string('landing_website_title')->default('RestroSaaS');
            $table->index('custom_domain');
            $table->integer('image_size')->default(5);
            $table->string('time_format')->default('H:i');
            $table->string('date_format')->default('Y-m-d');
            $table->index('vendor_id');
            $table->index('custom_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
