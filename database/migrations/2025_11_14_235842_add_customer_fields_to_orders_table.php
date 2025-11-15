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
            // Colonnes pour stocker les infos client (aliased pour compatibilité avec user_name, etc.)
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('vendor_id');
            }
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('orders', 'mobile')) {
                $table->string('mobile')->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('orders', 'address')) {
                $table->text('address')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('orders', 'building')) {
                $table->string('building')->nullable()->after('address');
            }
            if (!Schema::hasColumn('orders', 'landmark')) {
                $table->string('landmark')->nullable()->after('building');
            }
            if (!Schema::hasColumn('orders', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('landmark');
            }
            if (!Schema::hasColumn('orders', 'vendor_note')) {
                $table->text('vendor_note')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'mobile',
                'address',
                'building',
                'landmark',
                'pincode',
                'vendor_note'
            ]);
        });
    }
};
