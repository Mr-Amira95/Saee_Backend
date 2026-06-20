<?php

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SupportMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.' . $this->message->support_ticket_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'message'     => $this->message->message,
            'sender_name' => $this->message->sender_name,
            'sender_id'   => $this->message->sender_id,
            'is_read'     => $this->message->is_read,
            'sent_at'     => $this->message->created_at?->toISOString(),
            'ticket_id'   => $this->message->support_ticket_id,
        ];
    }
}
