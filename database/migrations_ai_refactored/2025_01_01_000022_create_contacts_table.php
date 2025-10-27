<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table: contacts
     * Purpose: Store customer contact/support messages
     * Original migrations: 2022_12_15_054853_create_contacts_table.php
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('name');
            $table->string('email');
            $table->string('mobile');
            $table->longText('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
