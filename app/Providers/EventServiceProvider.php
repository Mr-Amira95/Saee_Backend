<?php

namespace App\Providers;

use App\Events\DriverLocationUpdated;
use App\Listeners\TriggerRouteOptimizationOnDriverLocation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DriverLocationUpdated::class => [
            TriggerRouteOptimizationOnDriverLocation::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
