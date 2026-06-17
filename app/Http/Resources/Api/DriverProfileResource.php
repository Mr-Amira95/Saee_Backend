<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'national_id'         => $this->national_id,
            'license_number'      => $this->license_number,
            'license_expiry_date' => $this->license_expiry_date?->toDateString(),
            'vehicle_type'        => $this->vehicle_type,
            'vehicle_plate'       => $this->vehicle_plate,
            'avatar_path'         => $this->avatar_path,
            'is_available'        => $this->is_available,
            'current_latitude'    => $this->current_latitude,
            'current_longitude'   => $this->current_longitude,
        ];
    }
}
