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
            $table->unsignedBigInteger('client_delivery_invoice_id');
            $table->unsignedBigInteger('order_id');

            $table->foreign('client_delivery_invoice_id', 'cdio_invoice_id_fk')
                  ->references('id')->on('client_delivery_invoices')->cascadeOnDelete();
            $table->foreign('order_id', 'cdio_order_id_fk')
                  ->references('id')->on('orders')->cascadeOnDelete();

            $table->unique('order_id');
            $table->index('client_delivery_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_delivery_invoice_orders');
    }
};
