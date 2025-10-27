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
        // Ajouter reorder_id aux banners
        if (Schema::hasTable('banners') && !Schema::hasColumn('banners', 'reorder_id')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->integer('reorder_id')->default(0)->after('id');
            });
        }

        // Ajouter reorder_id aux blogs
        if (Schema::hasTable('blogs') && !Schema::hasColumn('blogs', 'reorder_id')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->integer('reorder_id')->default(0)->after('id');
            });
        }

        // Ajouter reorder_id aux payments
        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'reorder_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->integer('reorder_id')->default(0)->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['banners', 'blogs', 'payments'];

        foreach ($tables as $table_name) {
            if (Schema::hasTable($table_name) && Schema::hasColumn($table_name, 'reorder_id')) {
                Schema::table($table_name, function (Blueprint $table) {
                    $table->dropColumn('reorder_id');
                });
            }
        }
    }
};
