<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: settings
     * Purpose: Store application and vendor-specific settings
     * Original migrations: 2022_11_15_000000_create_settings_table.php, 2025_10_18_220850_add_whatsapp_chat_to_settings_table.php, 2025_10_18_220850_add_whatsapp_chat_to_settings_table.php, 2025_10_19_075700_add_social_media_links_to_settings_table.php, 2025_10_19_075700_add_social_media_links_to_settings_table.php, 2025_10_19_083355_add_cover_image_to_settings_table.php, 2025_10_19_083355_add_cover_image_to_settings_table.php, 2025_10_19_084513_add_tracking_id_to_settings_table.php, 2025_10_19_084513_add_tracking_id_to_settings_table.php, 2025_10_23_034256_add_missing_columns_to_settings_table.php, 2025_10_23_034256_add_missing_columns_to_settings_table.php, 2025_10_23_105500_add_notification_sound_to_settings_table.php, 2025_10_23_105500_add_notification_sound_to_settings_table.php, 2025_10_23_112500_add_languages_to_settings_table.php, 2025_10_23_112500_add_languages_to_settings_table.php
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigInteger('vendor_id')->default(1);
            $table->string('currency')->default('XOF');
            $table->enum('currency_position')->default('left');
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
            $table->string('language')->default('en');
            $table->string('template')->default('default');
            $table->integer('template_type')->default(1);
            $table->string('primary_color')->default('#181D31');
            $table->string('secondary_color')->default('#6096B4');
            $table->string('landing_website_title')->default('RestroSaaS');
            $table->string('custom_domain')->nullable();
            $table->integer('image_size')->default(5);
            $table->string('time_format')->default('H:i');
            $table->string('date_format')->default('Y-m-d');
            $table->integer('whatsapp_chat_on_off')->default(2)->comment('1 = Yes, 2 = No');
            $table->string('tracking_id')->nullable();
            $table->integer('tawk_on_off')->default(2)->comment('1 = Yes, 2 = No');
            $table->string('facebook_link')->nullable();
            $table->string('twitter_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('cover_image')->default('default-cover.png');
            $table->longText('firebase')->nullable();
            $table->text('item_message')->nullable();
            $table->string('interval_time')->nullable();
            $table->string('interval_type')->nullable();
            $table->string('order_prefix')->nullable();
            $table->integer('order_number_start')->default(1001);
            $table->longText('whatsapp_message')->nullable();
            $table->longText('telegram_message')->nullable();
            $table->string('notification_sound')->nullable()->default('notification.mp3');
            $table->string('languages')->nullable()->default('fr|en');

            // Indexes
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
