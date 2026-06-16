<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_location_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_profile_id')->constrained('driver_profiles')->cascadeOnDelete();

            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // When the point was captured on the device (not when it was stored)
            $table->timestamp('recorded_at');

            // Optional telemetry — populated by the mobile app later
            $table->decimal('speed', 6, 2)->nullable();    // km/h
            $table->decimal('heading', 5, 2)->nullable();  // degrees 0–360
            $table->decimal('accuracy', 7, 2)->nullable(); // GPS accuracy in metres

            $table->timestamps();

            // Primary query pattern: one driver, time range, ordered
            $table->index(['driver_profile_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_location_histories');
    }
};
