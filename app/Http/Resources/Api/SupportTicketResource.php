<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'ticket_number'   => $this->ticket_number,
            'title'           => $this->title,
            'status'          => $this->status,
            'priority'        => $this->priority,
            'order_id'        => $this->order_id,
            'unread_count'   => $this->whenNotNull($this->unread_count),
            'latest_message' => $this->when(
                $this->relationLoaded('messages'),
                fn () => $this->messages->last()?->message,
            ),
            'messages'       => SupportMessageResource::collection($this->whenLoaded('messages')),
            'created_at'      => $this->created_at->toISOString(),
            'updated_at'      => $this->updated_at->toISOString(),
        ];
    }
}
