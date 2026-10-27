<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('pickup_whatsapp_sent_at')->nullable()->after('returned_at');
            $table->index(['status', 'pickup_whatsapp_sent_at']);
        });

        // Orders already picked up were messaged immediately under the old flow,
        // so don't send them the pickup message again at the next 8 AM run.
        DB::table('orders')
            ->where('status', 'picked_up')
            ->update(['pickup_whatsapp_sent_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'pickup_whatsapp_sent_at']);
            $table->dropColumn('pickup_whatsapp_sent_at');
        });
    }
};
