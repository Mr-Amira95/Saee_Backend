<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use App\Realtime\RealtimeHub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverBankDetail extends Model
{
    use BroadcastsRealtimeChanges;

    protected $fillable = [
        'driver_profile_id',
        'bank_name',
        'account_name',
        'account_number',
        'iban',
        'swift_code',
        'cliq_id',
        'cliq_alias_type',
        'notes',
    ];

    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function realtimeAudience(): array
    {
        $hub = app(RealtimeHub::class);

        return [
            'drivers' => array_map(fn ($id) => $hub->driverUserId($id), $this->realtimeCurrentAndPrevious('driver_profile_id')),
        ];
    }
}
