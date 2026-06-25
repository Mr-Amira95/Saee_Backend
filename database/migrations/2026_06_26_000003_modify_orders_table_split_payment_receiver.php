<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key constraints first
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['area_id']);
        });

        // Drop moved columns
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'driver_id',
                // Payment fields
                'payment_type',
                'delivery_on_customer',
                'delivery_customer_amount',
                'delivery_amount',
                'order_price',
                // Receiver fields
                'receiver_name',
                'receiver_phone',
                'city_id',
                'area_id',
                'address_text',
                'address_location',
                'receiver_latitude',
                'receiver_longitude',
                'location_received_at',
            ]);
        });

        // Add new driver_profile_id column
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('driver_profile_id')
                ->nullable()
                ->after('client_profile_id')
                ->constrained('driver_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['driver_profile_id']);
            $table->dropColumn('driver_profile_id');

            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('payment_type', ['cod', 'prepaid'])->after('order_description');
            $table->boolean('delivery_on_customer')->default(false);
            $table->decimal('delivery_customer_amount', 10, 2)->nullable();
            $table->decimal('delivery_amount', 10, 2)->default(0);
            $table->decimal('order_price', 10, 2)->nullable();

            $table->string('receiver_name')->default('');
            $table->string('receiver_phone')->default('');
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('area_id')->constrained('areas');
            $table->text('address_text')->default('');
            $table->string('address_location')->nullable();
            $table->decimal('receiver_latitude', 10, 8)->nullable();
            $table->decimal('receiver_longitude', 11, 8)->nullable();
            $table->timestamp('location_received_at')->nullable();
        });
    }
};
