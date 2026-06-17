<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('client_profile_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('from_account'); // 'customer', 'driver', 'company', 'client'
            $table->string('to_account');   // 'driver', 'company', 'client'
            $table->decimal('amount', 10, 2);
            $table->string('type');         // 'cod_collection', 'delivery_collection', 'driver_settlement', 'client_payout', 'shipping_charge'
            
            $table->string('reference_number')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('from_account');
            $table->index('to_account');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledger_entries');
    }
};
