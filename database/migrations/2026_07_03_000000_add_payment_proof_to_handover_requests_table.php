<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('handover_requests', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('notes'); // 'cash', 'bank_transfer', 'cliq'
            $table->string('proof_image_path')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('handover_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'proof_image_path']);
        });
    }
};
