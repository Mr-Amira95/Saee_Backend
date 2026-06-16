<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_per_salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_salary_config_id')->constrained('driver_salary_configs')->cascadeOnDelete();
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('car_allowance', 10, 2);
            $table->smallInteger('extra_order_threshold')->unsigned();
            $table->decimal('extra_order_bonus', 8, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['driver_salary_config_id', 'effective_from'], 'dpsc_config_from_unique');
            $table->index(['driver_salary_config_id', 'effective_to'], 'dpsc_config_to_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_per_salary_configs');
    }
};
