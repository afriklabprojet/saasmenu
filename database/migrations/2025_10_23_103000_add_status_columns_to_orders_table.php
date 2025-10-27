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
        Schema::table('orders', function (Blueprint $table) {
            // Only add status_type, payment_status already exists from 2024_01_15_000006
            if (!Schema::hasColumn('orders', 'status_type')) {
                $table->tinyInteger('status_type')->default(1)->after('status')->comment('1=pending, 2=processing, 3=completed, 4=cancelled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'status_type')) {
                $table->dropColumn('status_type');
            }
        });
    }
};
