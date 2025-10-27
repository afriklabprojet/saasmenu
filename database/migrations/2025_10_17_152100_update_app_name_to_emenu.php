<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Mettre à jour le nom de l'application dans la configuration si la table existe
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->update(['website_title' => 'E-menu']);

            DB::table('settings')
                ->update(['landing_website_title' => 'E-menu - Digital Menu System']);
        }

        // Mettre à jour les métadonnées de l'application
        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')
                ->where('key', 'LIKE', '%app%name%')
                ->orWhere('key', 'LIKE', '%system%name%')
                ->update(['value' => 'E-menu']);
        }

        // Log de la mise à jour
        Log::info('Application name updated to E-menu', [
            'timestamp' => now(),
            'migration' => '2025_10_17_update_app_name_to_emenu'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revenir à l'ancien nom si nécessaire
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where('key', 'app_name')
                ->update(['value' => 'RestroSaaS']);

            DB::table('settings')
                ->where('key', 'app_title')
                ->update(['value' => 'RestroSaaS - Restaurant Management System']);
        }

        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')
                ->where('key', 'LIKE', '%app%name%')
                ->orWhere('key', 'LIKE', '%system%name%')
                ->update(['value' => 'RestroSaaS']);
        }

        Log::info('Application name reverted to RestroSaaS', [
            'timestamp' => now(),
            'migration' => '2025_10_17_update_app_name_to_emenu (rollback)'
        ]);
    }
};
