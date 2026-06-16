<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('role', ['superadmin', 'admin', 'driver', 'client_master', 'client_employee'])
                ->after('email_verified_at');
            $table->enum('status', ['active', 'suspended', 'pending'])
                ->default('active')
                ->after('role');
            $table->softDeletes();

            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['phone', 'role', 'status', 'deleted_at']);
        });
    }
};
