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
        Schema::table('users', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->after('slug');
            $table->boolean('domain_verified')->default(false)->after('custom_domain');
            $table->timestamp('domain_verified_at')->nullable()->after('domain_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['custom_domain', 'domain_verified', 'domain_verified_at']);
        });
    }
};
