<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceLedgerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'from_account'     => $this->from_account,
            'to_account'       => $this->to_account,
            'amount'           => (float) $this->amount,
            'reference_number' => $this->reference_number,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at->toDateTimeString(),
            'order'            => $this->whenLoaded('order', fn () => [
                'id'             => $this->order->id,
                'order_number'   => $this->order->order_number,
                'status'         => $this->order->status,
                'payment_type'   => $this->order->payment_type,
                'payment_status' => $this->order->payment_status,
                'order_price'    => (float) $this->order->order_price,
                'delivery_amount'=> (float) $this->order->delivery_amount,
            ]),
        ];
    }
}
