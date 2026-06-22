<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','with_driver','with_company','paid','no_payment') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert any with_company rows to with_driver before removing the value
        DB::table('orders')->where('payment_status', 'with_company')->update(['payment_status' => 'with_driver']);

        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','with_driver','paid','no_payment') DEFAULT 'pending'");
    }
};
