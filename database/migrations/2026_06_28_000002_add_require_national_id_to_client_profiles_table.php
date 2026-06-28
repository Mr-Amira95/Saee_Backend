<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->boolean('require_national_id')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('require_national_id');
        });
    }
};
