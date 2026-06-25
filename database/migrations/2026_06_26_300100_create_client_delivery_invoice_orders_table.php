<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_delivery_invoice_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_delivery_invoice_id')
                  ->constrained('client_delivery_invoices')
                  ->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->unique('order_id');
            $table->index('client_delivery_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_delivery_invoice_orders');
    }
};
