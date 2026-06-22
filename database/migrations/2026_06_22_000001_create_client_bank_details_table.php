<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->unique()->constrained('client_profiles')->cascadeOnDelete();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number', 30)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('swift_code', 11)->nullable();
            $table->string('cliq_id', 50)->nullable();
            $table->enum('cliq_alias_type', ['phone', 'national_id'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_bank_details');
    }
};
