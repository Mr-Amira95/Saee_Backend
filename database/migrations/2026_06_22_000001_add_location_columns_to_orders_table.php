<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('receiver_latitude', 10, 8)->nullable()->after('address_location');
            $table->decimal('receiver_longitude', 11, 8)->nullable()->after('receiver_latitude');
            $table->timestamp('location_received_at')->nullable()->after('receiver_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['receiver_latitude', 'receiver_longitude', 'location_received_at']);
        });
    }
};
