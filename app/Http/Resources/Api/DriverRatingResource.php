<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'order_number' => $this->order?->order_number,
            'rating'       => $this->rating,
            'comment'      => $this->comment,
            'created_at'   => $this->created_at->toISOString(),
        ];
    }
}
