<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_receivers', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->primary();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('area_id')->constrained('areas');
            $table->text('address_text');

            $table->decimal('receiver_latitude', 10, 8)->nullable();
            $table->decimal('receiver_longitude', 11, 8)->nullable();
            $table->timestamp('location_received_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_receivers');
    }
};
