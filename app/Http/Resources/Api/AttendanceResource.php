<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = match (true) {
            $this->check_out_at !== null => 'checked_out',
            $this->check_in_at !== null  => 'checked_in',
            default                      => 'not_checked_in',
        };

        $durationMinutes = null;
        if ($this->check_in_at && $this->check_out_at) {
            $durationMinutes = (int) $this->check_in_at->diffInMinutes($this->check_out_at);
        }

        return [
            'id'                  => $this->id,
            'date'                => $this->date->toDateString(),
            'status'              => $status,
            'check_in_at'         => $this->check_in_at?->toDateTimeString(),
            'check_out_at'        => $this->check_out_at?->toDateTimeString(),
            'check_in_location'   => $this->check_in_location,
            'check_out_location'  => $this->check_out_location,
            'duration_minutes'    => $durationMinutes,
        ];
    }
}
