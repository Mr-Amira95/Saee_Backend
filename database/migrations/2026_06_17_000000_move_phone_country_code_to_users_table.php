<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->nullable()->after('phone');
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn('phone_country_code');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('phone_country_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_country_code');
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->nullable()->after('user_id');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->nullable()->after('vat_number');
        });
    }
};
