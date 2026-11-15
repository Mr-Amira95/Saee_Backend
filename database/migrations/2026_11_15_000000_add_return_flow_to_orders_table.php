<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Return flow for rejected orders:
 *   rejected → in_office (handover approved) → out_for_return (return driver
 *   assigned) → returned (handed back to the client, who then confirms it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'assigned', 'picked_up', 'delivered', 'rejected', 'in_office', 'out_for_return', 'returned', 'cancelled') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('orders', function (Blueprint $table) {
            // Driver taking the order back to the client (driver_profile_id stays the delivery driver)
            $table->foreignId('return_driver_profile_id')->nullable()->after('driver_profile_id')
                ->constrained('driver_profiles')->nullOnDelete();

            // Who bears the delivery fee of a rejected order: not_charged | collected | client_charged.
            // Null = rejected before this feature (the client is charged, as before).
            $table->string('rejection_fee_status', 20)->nullable()->after('rejection_reason_id');

            $table->timestamp('return_confirmed_at')->nullable()->after('returned_at');
            $table->foreignId('return_confirmed_by')->nullable()->after('return_confirmed_at')
                ->constrained('users')->nullOnDelete();
        });

        // Orders returned before this feature never asked the client for a
        // confirmation: mark them as settled (no confirming user) so they do
        // not all show up as "awaiting confirmation".
        DB::table('orders')
            ->where('status', 'returned')
            ->update(['return_confirmed_at' => DB::raw('COALESCE(returned_at, updated_at)')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('return_driver_profile_id');
            $table->dropConstrainedForeignId('return_confirmed_by');
            $table->dropColumn(['rejection_fee_status', 'return_confirmed_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('orders')->where('status', 'in_office')->update(['status' => 'rejected']);
            DB::table('orders')->where('status', 'out_for_return')->update(['status' => 'rejected']);

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'assigned', 'picked_up', 'delivered', 'rejected', 'returned', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
