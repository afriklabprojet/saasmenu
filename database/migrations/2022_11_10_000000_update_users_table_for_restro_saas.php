<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableForRestroSaas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('type')->default(1)->after('email'); // 1=admin/vendor, 2=user, 3=customer, 4=employee
            $table->string('slug')->nullable()->after('name');
            $table->string('mobile')->nullable()->after('email');
            $table->string('image')->nullable()->after('mobile');
            $table->integer('is_available')->default(1)->after('image'); // 1=available, 2=unavailable
            $table->integer('is_deleted')->default(2)->after('is_available'); // 1=deleted, 2=not deleted
            $table->integer('vendor_id')->nullable()->after('type'); // for employees
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type', 'slug', 'mobile', 'image', 'is_available', 'is_deleted', 'vendor_id']);
        });
    }
}