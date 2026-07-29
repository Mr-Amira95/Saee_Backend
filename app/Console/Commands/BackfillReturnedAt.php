<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderTrackingLog;
use Illuminate\Console\Command;

class BackfillReturnedAt extends Command
{
    protected $signature   = 'orders:backfill-returned-at';
    protected $description = 'Backfill orders.returned_at for returned orders left null before the returnOrder() fix';

    public function handle(): int
    {
        $orders = Order::where('status', 'returned')->whereNull('returned_at')->get();

        if ($orders->isEmpty()) {
            $this->info('Nothing to backfill.');
            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($orders as $order) {
            $transition = OrderTrackingLog::where('order_id', $order->id)
                ->where('to_status', 'returned')
                ->latest('created_at')
                ->first();

            if (! $transition) {
                $this->warn("Order #{$order->id} ({$order->order_number}): no 'returned' tracking log found, skipped.");
                $skipped++;
                continue;
            }

            $order->update(['returned_at' => $transition->created_at]);
            $updated++;
        }

        $this->info("Backfilled {$updated} order(s), skipped {$skipped}.");

        return self::SUCCESS;
    }
}
