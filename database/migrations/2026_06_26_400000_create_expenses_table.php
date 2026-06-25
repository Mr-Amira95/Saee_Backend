<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('category', [
                'employee_salary',
                'rent',
                'utilities',
                'fuel',
                'vehicle_maintenance',
                'insurance',
                'marketing',
                'office_supplies',
                'other',
            ]);
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cliq', 'cheque']);
            $table->string('description', 500);
            $table->string('vendor', 255)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->string('receipt_path', 500)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
