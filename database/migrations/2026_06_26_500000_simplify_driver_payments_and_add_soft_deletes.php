<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_payments', function (Blueprint $table) {
            $table->softDeletes();
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at']);
        });

        DB::statement("ALTER TABLE driver_payments MODIFY COLUMN status ENUM('draft','paid') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE driver_payments MODIFY COLUMN status ENUM('draft','approved','paid') NOT NULL DEFAULT 'draft'");

        Schema::table('driver_payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unsignedBigInteger('approved_by')->nullable()->after('recorded_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }
};
