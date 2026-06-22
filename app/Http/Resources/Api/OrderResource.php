<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'order_number'             => $this->order_number,
            'status'                   => $this->status,
            'payment_type'             => $this->payment_type,
            'payment_status'           => $this->when($this->payment_type !== 'prepaid', $this->payment_status),
            'order_description'        => $this->order_description,
            'delivery_on_customer'     => $this->when($this->payment_type !== 'prepaid', (bool) $this->delivery_on_customer),
            'delivery_amount'          => $this->when($this->payment_type !== 'prepaid', (float) $this->delivery_amount),
            'delivery_customer_amount' => $this->when((bool) $this->delivery_on_customer, $this->delivery_customer_amount !== null ? (float) $this->delivery_customer_amount : null),
            'order_price'              => $this->when(
                $this->payment_type !== 'prepaid',
                $this->order_price !== null ? (float) $this->order_price : null
            ),
            'receiver_name'            => $this->receiver_name,
            'receiver_phone'           => $this->receiver_phone,
            'address_text'             => $this->address_text,
            'address_location'         => $this->address_location,
            'notes'                    => $this->notes,
            'signature_url'            => $this->signature_path
                ? Storage::disk('public')->url($this->signature_path)
                : null,
            'proof_image_url'          => $this->proof_image_path
                ? Storage::disk('public')->url($this->proof_image_path)
                : null,
            'city'                     => $this->whenLoaded('city', fn () => [
                'id'      => $this->city->id,
                'name'    => $this->city->name,
                'name_ar' => $this->city->name_ar,
            ]),
            'area'                     => $this->whenLoaded('area', fn () => [
                'id'      => $this->area->id,
                'name'    => $this->area->name,
                'name_ar' => $this->area->name_ar,
            ]),
            'driver'                   => $this->whenLoaded('driver', fn () => $this->driver ? [
                'id'   => $this->driver->id,
                'name' => $this->driver->name,
            ] : null),
            'client_profile'           => $this->whenLoaded('clientProfile', fn () => $this->clientProfile ? [
                'id'           => $this->clientProfile->id,
                'company_name' => $this->clientProfile->company_name,
            ] : null),
            'rejection_reason'         => $this->whenLoaded('rejectionReason', fn () => $this->rejectionReason ? [
                'id'        => $this->rejectionReason->id,
                'reason'    => $this->rejectionReason->reason,
                'reason_ar' => $this->rejectionReason->reason_ar,
            ] : null),
            'tracking_logs'            => $this->whenLoaded('trackingLogs', fn () => $this->trackingLogs->map(fn ($log) => [
                'id'          => $log->id,
                'from_status' => $log->from_status,
                'to_status'   => $log->to_status,
                'description' => $log->description,
                'latitude'    => $log->latitude !== null ? (float) $log->latitude : null,
                'longitude'   => $log->longitude !== null ? (float) $log->longitude : null,
                'changed_by'  => $log->relationLoaded('user') && $log->user
                    ? ['id' => $log->user->id, 'name' => $log->user->name]
                    : null,
                'created_at'  => $log->created_at->toDateTimeString(),
            ])),
            'created_at'               => $this->created_at?->toDateTimeString(),
            'updated_at'               => $this->updated_at?->toDateTimeString(),
        ];
    }
}
