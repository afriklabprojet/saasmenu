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
        Schema::table('pricing_plans', function (Blueprint $table) {
            // Ajouter les colonnes de limitation
            $table->integer('products_limit')->default(-1)->after('price')->comment('-1 = illimité');
            $table->integer('order_limit')->default(-1)->after('products_limit')->comment('-1 = illimité');
            $table->integer('categories_limit')->default(-1)->after('order_limit')->comment('-1 = illimité');
            $table->boolean('custom_domain')->default(false)->after('categories_limit');
            $table->boolean('analytics')->default(true)->after('custom_domain');
            $table->boolean('whatsapp_integration')->default(true)->after('analytics');
            $table->integer('staff_limit')->default(-1)->after('whatsapp_integration')->comment('-1 = illimité');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn([
                'products_limit',
                'order_limit',
                'categories_limit',
                'custom_domain',
                'analytics',
                'whatsapp_integration',
                'staff_limit'
            ]);
        });
    }
};
