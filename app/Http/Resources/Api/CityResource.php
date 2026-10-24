<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'name_ar'        => $this->name_ar,
            'delivery_price' => $this->client_delivery_price ?? (float) $this->delivery_price,
            'areas'          => AreaResource::collection($this->whenLoaded('areas')),
        ];
    }
}
