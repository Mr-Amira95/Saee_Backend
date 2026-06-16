<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('company_name');
            $table->string('company_name_ar')->nullable();
            $table->string('commercial_register_number', 100)->nullable();
            $table->timestamp('commercial_register_verified_at')->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->string('industry', 100)->nullable();

            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('logo_path', 500)->nullable();

            $table->string('address_line1')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 10)->default('SA');

            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00);

            $table->enum('status', ['active', 'suspended', 'pending_verification'])
                ->default('pending_verification');

            $table->softDeletes();
            $table->timestamps();

            $table->index('company_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
