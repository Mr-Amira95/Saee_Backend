<?php

namespace App\Observers;

use App\Jobs\OptimizeDriverRouteJob;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        // An order created already assigned to a driver and in picked_up state
        if ($order->driver_id && $order->status === 'picked_up') {
            $this->dispatch($order->driver_id);
        }
    }

    public function updated(Order $order): void
    {
        if (! $order->driver_id) {
            return;
        }

        $statusChanged = $order->wasChanged('status');
        $driverChanged = $order->wasChanged('driver_id');

        if (! $statusChanged && ! $driverChanged) {
            return;
        }

        $newStatus = $order->status;
        $oldStatus = $order->getOriginal('status');

        $becamePickedUp  = $statusChanged && $newStatus === 'picked_up';
        $leftPickedUp    = $statusChanged
            && $oldStatus === 'picked_up'
            && in_array($newStatus, ['delivered', 'rejected', 'cancelled', 'returned'], true);

        // Driver was reassigned while order is still active
        $reassignedPickedUp = $driverChanged && $newStatus === 'picked_up';

        if ($becamePickedUp || $leftPickedUp || $reassignedPickedUp) {
            $this->dispatch($order->driver_id);

            // Also re-optimize the previous driver's route when an order is reassigned
            if ($driverChanged) {
                $oldDriverId = $order->getOriginal('driver_id');
                if ($oldDriverId && $oldDriverId !== $order->driver_id) {
                    $this->dispatch($oldDriverId);
                }
            }
        }
    }

    private function dispatch(int $driverId): void
    {
        OptimizeDriverRouteJob::dispatch($driverId)
            ->delay(now()->addSeconds(60))
            ->onQueue('default');
    }
}
