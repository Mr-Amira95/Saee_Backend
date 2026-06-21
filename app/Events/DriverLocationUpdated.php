<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int    $driverProfileId,
        public string $driverName,
        public float  $latitude,
        public float  $longitude,
        public string $updatedAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin.drivers'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'driver_id'  => $this->driverProfileId,
            'name'       => $this->driverName,
            'latitude'   => $this->latitude,
            'longitude'  => $this->longitude,
            'updated_at' => $this->updatedAt,
        ];
    }
}
