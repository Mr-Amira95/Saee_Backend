<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_employee_permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('granted_by');
            $table->foreign('granted_by')->references('id')->on('users');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['employee_user_id', 'permission_id', 'client_profile_id'],
                'emp_perm_client_unique'
            );
            $table->index(['employee_user_id', 'client_profile_id'], 'emp_user_client_idx');
            $table->index('permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_employee_permission_user');
    }
};
