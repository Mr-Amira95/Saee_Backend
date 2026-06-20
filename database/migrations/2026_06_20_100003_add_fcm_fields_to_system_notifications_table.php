<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            // null = no FCM push attempted, 'sent' = all delivered,
            // 'partial' = some failed, 'failed' = all failed, 'skipped' = no tokens
            $table->string('fcm_status', 20)->nullable()->after('read_at');
            $table->unsignedSmallInteger('fcm_sent_count')->nullable()->after('fcm_status');
            $table->unsignedSmallInteger('fcm_failed_count')->nullable()->after('fcm_sent_count');
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropColumn(['fcm_status', 'fcm_sent_count', 'fcm_failed_count']);
        });
    }
};
