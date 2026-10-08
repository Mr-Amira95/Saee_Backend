<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use App\Realtime\RealtimeHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    use BroadcastsRealtimeChanges;

    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'order_id',
        'payment_type',
        'order_amount',
        'delivery_on_customer',
        'customer_delivery_amount',
        'client_delivery_amount',
    ];

    protected function casts(): array
    {
        return [
            'order_amount'             => 'decimal:2',
            'customer_delivery_amount' => 'decimal:2',
            'client_delivery_amount'   => 'decimal:2',
            'delivery_on_customer'     => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function realtimeAudience(): array
    {
        return app(RealtimeHub::class)->orderAudience($this->order_id);
    }
}
