<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'master_user_id'  => $this->master_user_id,
            'company_name'    => $this->company_name,
            'company_name_ar' => $this->company_name_ar,
            'email'           => $this->email,
            'city_id'            => $this->city_id,
            'area_id'            => $this->area_id,
            'address_line1'      => $this->address_line1,
            'credit_limit'       => $this->credit_limit,
            'balance'            => $this->balance,
            'status'             => $this->status,
        ];
    }
}
