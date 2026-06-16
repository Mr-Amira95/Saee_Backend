<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_order_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->decimal('rate', 8, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['city_id', 'effective_from']);
            $table->index(['city_id', 'effective_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_order_rates');
    }
};
