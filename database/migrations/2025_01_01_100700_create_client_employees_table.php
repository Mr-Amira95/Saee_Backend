<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')
                ->nullable()
                ->constrained('client_employee_invitations')
                ->nullOnDelete();

            $table->string('job_title', 100)->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_employees');
    }
};
