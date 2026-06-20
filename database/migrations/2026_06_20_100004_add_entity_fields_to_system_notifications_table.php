<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            // entity_type / entity_id — identifies the related record so a redirect URL
            // can be resolved (e.g. entity_type='support_ticket', entity_id=42).
            // The resolved URL is also pre-stored in the existing `link` column.
            $table->string('entity_type', 60)->nullable()->after('link');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropColumn(['entity_type', 'entity_id']);
        });
    }
};
