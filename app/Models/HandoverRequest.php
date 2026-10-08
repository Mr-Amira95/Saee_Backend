<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HandoverRequest extends Model
{
    use BroadcastsRealtimeChanges;

    protected $fillable = [
        'driver_id',
        'status', // 'pending', 'approved'
        'notes',
        'payment_method', // 'cash', 'bank_transfer', 'cliq'
        'proof_image_path',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function realtimeAudience(): array
    {
        return ['drivers' => $this->realtimeCurrentAndPrevious('driver_id')];
    }
}
