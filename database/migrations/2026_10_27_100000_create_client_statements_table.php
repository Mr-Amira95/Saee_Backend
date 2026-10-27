<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Client statements replace the old COD Invoices / Client Billing / Payout flow.
 *
 * One statement = one exported report for one client (COD + customer delivery
 * fees − Saee delivery fees). Exporting moves the orders to payment_status
 * "exported"; confirming the statement's payment moves them to "paid".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','with_driver','with_company','exported','paid','no_payment') DEFAULT 'pending'");
        }

        Schema::create('client_statements', function (Blueprint $table) {
            $table->id();
            $table->string('statement_number', 30)->unique();
            $table->foreignId('client_profile_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_cod', 12, 2)->default(0);
            $table->decimal('total_customer_delivery', 12, 2)->default(0);
            $table->decimal('total_client_delivery', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->enum('status', ['exported', 'paid', 'superseded'])->default('exported');
            $table->foreignId('exported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_profile_id', 'status']);
        });

        Schema::create('client_statement_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_statement_id')->constrained('client_statements')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            // Payment status before the export, restored if the statement is superseded
            $table->string('previous_payment_status', 20);
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->decimal('customer_delivery_amount', 10, 2)->default(0);
            $table->decimal('client_delivery_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);

            $table->unique(['client_statement_id', 'order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('client_statement_id')->nullable()->after('handover_request_id')
                ->constrained('client_statements')->nullOnDelete();
        });

        // Orders whose delivery fee was already settled through a paid
        // Client Billing invoice must not show up again as ready to export.
        DB::table('orders')
            ->where('payment_status', 'no_payment')
            ->whereIn('id', DB::table('client_delivery_invoice_orders')
                ->join('client_delivery_invoices', 'client_delivery_invoices.id', '=', 'client_delivery_invoice_orders.client_delivery_invoice_id')
                ->where('client_delivery_invoices.status', 'paid')
                ->select('client_delivery_invoice_orders.order_id'))
            ->update(['payment_status' => 'paid']);
    }

    public function down(): void
    {
        // Put exported orders back to the payment status they had before export
        DB::table('client_statement_orders')
            ->join('orders', 'orders.id', '=', 'client_statement_orders.order_id')
            ->where('orders.payment_status', 'exported')
            ->whereColumn('orders.client_statement_id', 'client_statement_orders.client_statement_id')
            ->select('client_statement_orders.order_id', 'client_statement_orders.previous_payment_status')
            ->get()
            ->each(fn ($row) => DB::table('orders')->where('id', $row->order_id)
                ->update(['payment_status' => $row->previous_payment_status]));

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_statement_id');
        });

        Schema::dropIfExists('client_statement_orders');
        Schema::dropIfExists('client_statements');

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','with_driver','with_company','paid','no_payment') DEFAULT 'pending'");
        }
    }
};
