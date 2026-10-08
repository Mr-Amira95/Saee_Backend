<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Batched "something changed" notification sent by App\Realtime\RealtimeHub.
 * Sent synchronously, but the hub only fires it after the response has gone out.
 */
class RealtimeChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @param  array<int, array{type: string, id: int|string|null, action: string, by: int|null}>  $changes
     * @param  array<int, string>  $types
     */
    public function __construct(
        public string $channel,
        public array  $changes,
        public array  $types,
        public bool   $bulk = false,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->channel),
        ];
    }

    public function broadcastAs(): string
    {
        return 'changed';
    }

    public function broadcastWith(): array
    {
        return [
            'changes' => $this->changes,
            'types'   => $this->types,
            'bulk'    => $this->bulk,
            'at'      => now()->toISOString(),
        ];
    }
}
