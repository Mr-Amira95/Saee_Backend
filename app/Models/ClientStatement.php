<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * One exported financial statement for one client: per-order COD + customer
 * delivery fees − Saee delivery fees. See App\Services\ClientStatementService.
 */
class ClientStatement extends Model
{
    protected $fillable = [
        'statement_number',
        'client_profile_id',
        'date_from',
        'date_to',
        'orders_count',
        'total_cod',
        'total_customer_delivery',
        'total_client_delivery',
        'net_amount',
        'status', // 'exported', 'paid', 'superseded'
        'exported_by',
        'paid_by',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_from'               => 'date',
            'date_to'                 => 'date',
            'total_cod'               => 'decimal:2',
            'total_customer_delivery' => 'decimal:2',
            'total_client_delivery'   => 'decimal:2',
            'net_amount'              => 'decimal:2',
            'paid_at'                 => 'datetime',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class)->withTrashed();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'client_statement_orders')
            ->withPivot(['previous_payment_status', 'cod_amount', 'customer_delivery_amount', 'client_delivery_amount', 'net_amount']);
    }

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
