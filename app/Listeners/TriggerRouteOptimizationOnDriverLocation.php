<?php

namespace App\Listeners;

use App\Events\DriverLocationUpdated;
use App\Jobs\OptimizeDriverRouteJob;
use App\Models\DriverProfile;

class TriggerRouteOptimizationOnDriverLocation
{
    public function handle(DriverLocationUpdated $event): void
    {
        // event->driverProfileId is driver_profiles.id, but Order::driver_id = users.id
        $profile = DriverProfile::find($event->driverProfileId);

        if (! $profile) {
            return;
        }

        OptimizeDriverRouteJob::dispatch($profile->user_id)
            ->delay(now()->addSeconds(60))
            ->onQueue('default');
    }
}
