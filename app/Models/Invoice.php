<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_profile_id',
        'payout_ledger_entry_id',
        'total_orders',
        'cod_amount',
        'shipping_amount',
        'customer_delivery_amount',
        'net_amount',
        'status',
        'notes',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'cod_amount'               => 'decimal:2',
            'shipping_amount'          => 'decimal:2',
            'customer_delivery_amount' => 'decimal:2',
            'net_amount'               => 'decimal:2',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function payoutLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(FinancialLedgerEntry::class, 'payout_ledger_entry_id');
    }

    public function orders(): HasMany
    {
        // An invoice can span multiple orders whose client_payout ledger entries are linked or orders paid in this billing payout
        // Let's hook a direct relationship via ClientProfile or through the payout ledger entry
        return $this->hasMany(Order::class, 'client_profile_id', 'client_profile_id')
            ->where('payment_status', 'paid');
    }
}
