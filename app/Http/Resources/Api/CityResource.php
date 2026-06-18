<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'name_ar' => $this->name_ar,
            'areas'   => AreaResource::collection($this->whenLoaded('areas')),
        ];
    }
}
