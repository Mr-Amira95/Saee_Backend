<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use App\Realtime\RealtimeHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverRating extends Model
{
    use BroadcastsRealtimeChanges;

    protected $table = 'driver_ratings';

    protected $fillable = [
        'order_id',
        'driver_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function realtimeAudience(): array
    {
        $audience = app(RealtimeHub::class)->orderAudience($this->order_id);
        $audience['drivers'][] = $this->driver_id;

        return $audience;
    }
}
