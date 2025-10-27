<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: pricing_plans
     * Purpose: Store pricing plans data
     * Original migrations: 2025_10_18_201443_create_pricing_plans_table.php, 2025_10_23_041541_add_limits_to_pricing_plans_table.php, 2025_10_23_041541_add_limits_to_pricing_plans_table.php
     */
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('features')->nullable();
            $table->decimal('price')->default(0);
            $table->integer('duration')->default(30);
            $table->integer('service_limit')->default(-1);
            $table->integer('appoinment_limit')->default(-1);
            $table->enum('type')->default('monthly');
            $table->boolean('is_available')->default(1);
            $table->integer('products_limit')->default(-1)->comment('-1 = illimité');
            $table->integer('order_limit')->default(-1)->comment('-1 = illimité');
            $table->integer('categories_limit')->default(-1)->comment('-1 = illimité');
            $table->boolean('custom_domain')->default(false);
            $table->boolean('analytics')->default(true);
            $table->boolean('whatsapp_integration')->default(true);
            $table->integer('staff_limit')->default(-1)->comment('-1 = illimité');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
