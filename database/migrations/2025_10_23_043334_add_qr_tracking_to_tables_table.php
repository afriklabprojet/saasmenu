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
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('scan_count')->default(0)->after('status');
            $table->timestamp('last_scanned_at')->nullable()->after('scan_count');
            $table->string('qr_color_fg', 7)->default('#000000')->after('last_scanned_at')->comment('Couleur avant-plan QR code');
            $table->string('qr_color_bg', 7)->default('#FFFFFF')->after('qr_color_fg')->comment('Couleur arrière-plan QR code');
            $table->boolean('qr_use_logo')->default(true)->after('qr_color_bg')->comment('Utiliser logo restaurant dans QR');
            $table->unsignedInteger('qr_size')->default(300)->after('qr_use_logo')->comment('Taille QR code en pixels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn([
                'scan_count',
                'last_scanned_at',
                'qr_color_fg',
                'qr_color_bg',
                'qr_use_logo',
                'qr_size'
            ]);
        });
    }
};
