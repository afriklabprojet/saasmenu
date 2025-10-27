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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_id')->nullable();
            $table->string('payment_type')->default('1'); // 1=COD, 2=Online, etc.
            $table->enum('status', ['1', '2', '3'])->default('2'); // 1=pending, 2=success, 3=failed
            $table->date('expire_date')->nullable();
            $table->integer('service_limit')->default(-1);
            $table->integer('appoinment_limit')->default(-1);
            $table->text('response')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
