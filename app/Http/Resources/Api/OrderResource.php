<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payment = $this->relationLoaded('payment') ? $this->payment : null;
        $receiver = $this->relationLoaded('receiver') ? $this->receiver : null;

        $paymentType = $payment?->payment_type;

        return [
            'id'                       => $this->id,
            'order_number'             => $this->order_number,
            'status'                   => $this->status,
            'payment_type'             => $paymentType,
            'payment_status'           => $this->when($paymentType !== 'prepaid', $this->payment_status),
            'order_description'        => $this->order_description,
            'delivery_on_customer'     => $this->when($paymentType !== 'prepaid', $payment ? (bool) $payment->delivery_on_customer : null),
            'delivery_amount'          => $this->when($paymentType !== 'prepaid', $payment ? (float) $payment->client_delivery_amount : null),
            'delivery_customer_amount' => $this->when($payment && (bool) $payment->delivery_on_customer, $payment?->customer_delivery_amount !== null ? (float) $payment->customer_delivery_amount : null),
            'order_price'              => $this->when(
                $paymentType !== 'prepaid',
                $payment?->order_amount !== null ? (float) $payment->order_amount : null
            ),
            'receiver_name'            => $receiver?->receiver_name,
            'receiver_phone'           => $receiver?->receiver_phone,
            'address_text'             => $receiver?->address_text,
            'notes'                    => $this->notes,
            'signature_url'            => $this->signature_path
                ? Storage::disk('public')->url($this->signature_path)
                : null,
            'proof_image_url'          => $this->proof_image_path
                ? Storage::disk('public')->url($this->proof_image_path)
                : null,
            'national_id_attachment_url' => $this->national_id_attachment_path
                ? Storage::disk('public')->url($this->national_id_attachment_path)
                : null,
            'city'                     => $this->whenLoaded('receiver', fn () => $this->receiver?->city ? [
                'id'      => $this->receiver->city->id,
                'name'    => $this->receiver->city->name,
                'name_ar' => $this->receiver->city->name_ar,
            ] : null),
            'area'                     => $this->whenLoaded('receiver', fn () => $this->receiver?->area ? [
                'id'      => $this->receiver->area->id,
                'name'    => $this->receiver->area->name,
                'name_ar' => $this->receiver->area->name_ar,
            ] : null),
            'driver'                   => $this->whenLoaded('driverProfile', fn () => $this->driverProfile?->user ? [
                'id'   => $this->driverProfile->user->id,
                'name' => $this->driverProfile->user->name,
            ] : null),
            'client_profile'           => $this->whenLoaded('clientProfile', fn () => $this->clientProfile ? [
                'id'                   => $this->clientProfile->id,
                'company_name'         => $this->clientProfile->company_name,
                'require_national_id'  => (bool) $this->clientProfile->require_national_id,
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
