<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('user_id', 'attendances_user_id_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_id_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['user_id', 'date']);
            $table->dropIndex('attendances_user_id_index');
        });
    }
};
