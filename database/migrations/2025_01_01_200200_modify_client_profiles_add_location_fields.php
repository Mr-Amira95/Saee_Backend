<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->default('+962')->after('phone');
            $table->unsignedBigInteger('city_id')->nullable()->after('logo_path');
            $table->unsignedBigInteger('area_id')->nullable()->after('city_id');
            $table->date('expiry_date')->nullable()->after('status');

            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();

            $table->dropColumn(['city', 'country']);
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['area_id']);
            $table->dropColumn(['phone_country_code', 'city_id', 'area_id', 'expiry_date']);
            $table->string('city', 100)->nullable();
            $table->string('country', 10)->default('SA')->nullable();
        });
    }
};
