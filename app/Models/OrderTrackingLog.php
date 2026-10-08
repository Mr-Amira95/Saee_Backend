<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use App\Realtime\RealtimeHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTrackingLog extends Model
{
    use BroadcastsRealtimeChanges;

    protected $fillable = [
        'order_id',
        'user_id',
        'from_status',
        'to_status',
        'description',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function realtimeAudience(): array
    {
        return app(RealtimeHub::class)->orderAudience($this->order_id);
    }
}
