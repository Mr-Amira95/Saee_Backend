<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn(['vehicle_model', 'vehicle_color', 'vehicle_capacity_kg']);
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->nullable()->after('user_id');
            $table->string('license_attachment')->nullable()->after('license_class');
            $table->date('car_license_expiry')->nullable()->after('vehicle_plate');
            $table->string('car_license_attachment')->nullable()->after('car_license_expiry');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn(['industry', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn(['phone_country_code', 'license_attachment', 'car_license_expiry', 'car_license_attachment']);
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->string('vehicle_model', 100)->nullable();
            $table->string('vehicle_color', 50)->nullable();
            $table->decimal('vehicle_capacity_kg', 8, 2)->nullable();
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('industry', 100)->nullable();
            $table->string('phone', 20)->default('');
        });
    }
};
