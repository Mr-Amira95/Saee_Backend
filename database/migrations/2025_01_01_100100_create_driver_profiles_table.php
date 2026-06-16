<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('national_id', 20)->unique();
            $table->timestamp('national_id_verified_at')->nullable();

            $table->string('license_number', 50)->unique();
            $table->date('license_expiry_date');
            $table->string('license_class', 20)->nullable();

            $table->string('vehicle_type', 50)->nullable();
            $table->string('vehicle_plate', 20)->nullable()->unique();
            $table->string('vehicle_model', 100)->nullable();
            $table->string('vehicle_color', 50)->nullable();
            $table->decimal('vehicle_capacity_kg', 8, 2)->nullable();

            $table->string('avatar_path', 500)->nullable();
            $table->boolean('is_available')->default(true);

            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('location_updated_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('is_available');
            $table->index('license_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
