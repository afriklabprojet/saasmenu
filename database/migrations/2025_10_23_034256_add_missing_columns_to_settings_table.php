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
        Schema::table('settings', function (Blueprint $table) {
            // Ajouter les colonnes manquantes trouvées dans le SQL de backup
            $table->longText('firebase')->nullable()->after('og_image');
            $table->text('item_message')->nullable()->after('delivery_type');
            $table->string('interval_time')->nullable()->after('item_message');
            $table->string('interval_type')->nullable()->after('interval_time');
            $table->string('order_prefix')->nullable()->after('date_format');
            $table->integer('order_number_start')->default(1001)->after('order_prefix');
            $table->longText('whatsapp_message')->nullable()->after('order_number_start');
            $table->longText('telegram_message')->nullable()->after('whatsapp_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'firebase',
                'item_message',
                'interval_time',
                'interval_type',
                'order_prefix',
                'order_number_start',
                'whatsapp_message',
                'telegram_message'
            ]);
        });
    }
};
