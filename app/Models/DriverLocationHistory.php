<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocationHistory extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'latitude',
        'longitude',
        'recorded_at',
        'speed',
        'heading',
        'accuracy',
    ];

    protected function casts(): array
    {
        return [
            'latitude'    => 'decimal:8',
            'longitude'   => 'decimal:8',
            'recorded_at' => 'datetime',
            'speed'       => 'decimal:2',
            'heading'     => 'decimal:2',
            'accuracy'    => 'decimal:2',
        ];
    }

    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }
}
