<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinancialLedgerEntry extends Model
{
    protected $fillable = [
        'order_id',
        'client_profile_id',
        'driver_id',
        'from_account',
        'to_account',
        'amount',
        'type',
        'reference_number',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'payout_ledger_entry_id');
    }
}
