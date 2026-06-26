<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverBankDetail extends Model
{
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
}
