<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->default(0)->after('avatar_path');
            $table->decimal('car_allowance', 10, 2)->default(0)->after('basic_salary');
            $table->unsignedSmallInteger('daily_order_threshold')->default(0)->after('car_allowance');
            $table->decimal('bonus_per_extra_order', 10, 2)->default(0)->after('daily_order_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'car_allowance', 'daily_order_threshold', 'bonus_per_extra_order']);
        });
    }
};
