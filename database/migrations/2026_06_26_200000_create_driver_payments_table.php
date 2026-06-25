<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_profile_id')->constrained('driver_profiles')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('car_allowance', 10, 2)->default(0);
            $table->unsignedSmallInteger('order_count')->default(0);
            $table->unsignedSmallInteger('extra_orders_count')->default(0);
            $table->decimal('extra_order_bonus', 10, 2)->default(0);
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cliq']);
            $table->string('reference_number', 100)->nullable();
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['driver_profile_id', 'period_start', 'period_end']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_payments');
    }
};
