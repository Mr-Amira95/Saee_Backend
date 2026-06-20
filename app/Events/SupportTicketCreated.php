<?php

namespace App\Events;

use App\Models\SupportTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('support-admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'status'        => $this->ticket->status,
            'user_name'     => $this->ticket->user?->name ?? 'Unknown',
            'updated_at'    => $this->ticket->updated_at?->toISOString(),
        ];
    }
}
