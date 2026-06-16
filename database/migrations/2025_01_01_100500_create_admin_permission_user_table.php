<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('granted_by');
            $table->foreign('granted_by')->references('id')->on('users');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['admin_user_id', 'permission_id'], 'admin_perm_unique');
            $table->index('admin_user_id');
            $table->index('permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permission_user');
    }
};
