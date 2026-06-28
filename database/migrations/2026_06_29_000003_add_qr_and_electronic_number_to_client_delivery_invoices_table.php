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
        Schema::table('client_delivery_invoices', function (Blueprint $table) {
            $table->string('qr_attachment_path')->nullable()->after('notes');
            $table->string('electronic_invoice_number')->nullable()->after('qr_attachment_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_delivery_invoices', function (Blueprint $table) {
            $table->dropColumn(['qr_attachment_path', 'electronic_invoice_number']);
        });
    }
};
