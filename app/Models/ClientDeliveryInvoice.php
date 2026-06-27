<?php

namespace App\Models;

use App\Enums\DeliveryInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientDeliveryInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_profile_id',
        'period_start',
        'period_end',
        'total_orders',
        'billable_orders',
        'delivery_amount',
        'discount_amount',
        'net_amount',
        'due_date',
        'paid_at',
        'payment_method',
        'reference_number',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'client_profile_id' => 'integer',
            'created_by' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'delivery_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'status' => DeliveryInvoiceStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'client_delivery_invoice_orders');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', DeliveryInvoiceStatus::Draft);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', DeliveryInvoiceStatus::Issued);
    }

    public function scopePaid($query)
    {
        return $query->where('status', DeliveryInvoiceStatus::Paid);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', DeliveryInvoiceStatus::Overdue);
    }
}
