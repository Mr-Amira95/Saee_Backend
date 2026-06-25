<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->primary();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            $table->enum('payment_type', ['cod', 'prepaid']);
            $table->decimal('order_amount', 10, 2)->nullable();
            $table->boolean('delivery_on_customer')->default(false);
            $table->decimal('customer_delivery_amount', 10, 2)->nullable();
            $table->decimal('client_delivery_amount', 10, 2);

            $table->timestamps();
        });

        DB::statement("ALTER TABLE order_payments
            ADD CONSTRAINT chk_cod_amount
            CHECK (payment_type != 'cod' OR order_amount IS NOT NULL)");

        DB::statement("ALTER TABLE order_payments
            ADD CONSTRAINT chk_customer_delivery_amount
            CHECK (delivery_on_customer = 0 OR customer_delivery_amount IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
