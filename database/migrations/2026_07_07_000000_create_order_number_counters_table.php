<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_number_counters', function (Blueprint $table) {
            $table->string('prefix', 20)->primary();
            $table->unsignedInteger('next_sequence')->default(1);
        });

        // Seed counters from existing orders so numbering continues from the
        // highest sequence already used per prefix, instead of restarting at
        // 1 and immediately colliding with rows created before this migration.
        $rows = DB::table('orders')
            ->selectRaw('SUBSTRING(order_number, 1, LENGTH(order_number) - 4) as prefix, MAX(CAST(SUBSTRING(order_number, -4) AS UNSIGNED)) as max_seq')
            ->whereNotNull('order_number')
            ->groupBy('prefix')
            ->get();

        foreach ($rows as $row) {
            DB::table('order_number_counters')->updateOrInsert(
                ['prefix' => $row->prefix],
                ['next_sequence' => $row->max_seq + 1]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_number_counters');
    }
};
