<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('driver_per_salary_configs');
        Schema::dropIfExists('driver_salary_configs');
    }

    public function down(): void
    {
        Schema::create('driver_salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_profile_id')->constrained('driver_profiles')->cascadeOnDelete();
            $table->enum('salary_type', ['per_salary', 'per_order']);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('driver_per_salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_salary_config_id')->constrained('driver_salary_configs')->cascadeOnDelete();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('car_allowance', 10, 2)->default(0);
            $table->unsignedSmallInteger('extra_order_threshold')->default(0);
            $table->decimal('extra_order_bonus', 10, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
    }
};
