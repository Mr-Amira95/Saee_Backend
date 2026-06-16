<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_delivery_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->decimal('delivery_price', 10, 2);
            $table->timestamps();

            $table->unique(['client_profile_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_delivery_prices');
    }
};
