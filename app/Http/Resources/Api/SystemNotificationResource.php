<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'message'     => $this->message,
            'type'        => $this->type,
            'link'        => $this->link,
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
            'is_read'     => !is_null($this->read_at),
            'read_at'     => $this->read_at?->toISOString(),
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
