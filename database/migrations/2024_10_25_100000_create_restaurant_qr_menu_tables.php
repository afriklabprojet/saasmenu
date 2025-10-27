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
        Schema::create('restaurant_qr_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('qr_code_path');
            $table->string('menu_url');
            $table->json('table_numbers')->nullable(); // Tables spécifiques
            $table->json('settings')->nullable(); // Paramètres QR (couleur, taille, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['vendor_id', 'is_active']);
            $table->index('slug');
        });

        Schema::create('qr_menu_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qr_menu_id');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('table_number')->nullable();
            $table->json('location_data')->nullable(); // Géolocalisation si disponible
            $table->timestamp('scanned_at');
            $table->timestamps();

            $table->foreign('qr_menu_id')->references('id')->on('restaurant_qr_menus')->onDelete('cascade');
            $table->index(['qr_menu_id', 'scanned_at']);
        });

        Schema::create('qr_menu_designs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('background_color')->default('#ffffff');
            $table->string('foreground_color')->default('#000000');
            $table->integer('size')->default(300);
            $table->string('format')->default('png');
            $table->json('custom_settings')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_menu_scans');
        Schema::dropIfExists('qr_menu_designs');
        Schema::dropIfExists('restaurant_qr_menus');
    }
};
