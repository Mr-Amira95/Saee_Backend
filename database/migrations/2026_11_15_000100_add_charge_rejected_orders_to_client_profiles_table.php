<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            // Whether a rejected order's delivery fee is still payable. Defaults
            // to true so existing clients keep being charged, as before.
            $table->boolean('charge_rejected_orders')->default(true)->after('require_national_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('charge_rejected_orders');
        });
    }
};
