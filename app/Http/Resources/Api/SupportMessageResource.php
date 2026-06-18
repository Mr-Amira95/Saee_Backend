<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'message'     => $this->message,
            'sender_name' => $this->sender_name,
            'is_mine'     => $this->sender_id === $request->user()?->id,
            'is_read'     => $this->is_read,
            'sent_at'     => $this->created_at->toISOString(),
        ];
    }
}
