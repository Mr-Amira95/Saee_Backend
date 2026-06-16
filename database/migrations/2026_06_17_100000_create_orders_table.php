<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('client_profile_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_description')->nullable();
            
            // Financial Details
            $table->enum('payment_type', ['cod', 'prepaid']);
            $table->boolean('delivery_on_customer')->default(false);
            $table->decimal('delivery_customer_amount', 10, 2)->nullable();
            $table->decimal('delivery_amount', 10, 2);
            $table->decimal('order_price', 10, 2)->nullable();
            
            // Receiver details
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('area_id')->constrained('areas');
            $table->text('address_text');
            $table->string('address_location')->nullable();
            
            // Statuses
            $table->enum('status', ['pending', 'picked_up', 'delivered', 'rejected', 'returned', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'with_driver', 'paid', 'no_payment'])->default('pending');
            
            // Verification / Failure info
            $table->string('signature_path')->nullable();
            $table->string('proof_image_path')->nullable();
            $table->foreignId('rejection_reason_id')->nullable()->constrained('rejection_reasons')->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('order_number');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
