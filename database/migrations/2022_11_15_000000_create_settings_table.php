<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vendor_id')->default(1);
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
            $table->string('custom_domain')->nullable();
            $table->integer('image_size')->default(5);
            $table->string('time_format')->default('H:i');
            $table->string('date_format')->default('Y-m-d');
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('custom_domain');
        });

        // Insert default settings
        DB::table('settings')->insert([
            'vendor_id' => 1,
            'currency' => 'XOF',
            'currency_position' => 'left',
            'currency_space' => 1,
            'decimal_separator' => 1,
            'currency_formate' => 2,
            'maintenance_mode' => 0,
            'delivery_type' => '1,2',
            'timezone' => 'UTC',
            'website_title' => 'RestroSaaS',
            'meta_title' => 'RestroSaaS - Restaurant Management System',
            'meta_description' => 'Complete restaurant management solution with addons',
            'language' => 'en',
            'template' => 'default',
            'template_type' => 1,
            'primary_color' => '#181D31',
            'secondary_color' => '#6096B4',
            'landing_website_title' => 'RestroSaaS',
            'image_size' => 5,
            'time_format' => 'H:i',
            'date_format' => 'Y-m-d',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
