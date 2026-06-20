<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int     $userId,
        public string  $title,
        public string  $message,
        public string  $type       = 'info',
        public ?string $link       = null,
        public ?string $entityType = null,
        public ?int    $entityId   = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('user-notifications.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    public function broadcastWith(): array
    {
        return [
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'link'        => $this->link,
            'entity_type' => $this->entityType,
            'entity_id'   => $this->entityId,
        ];
    }
}
