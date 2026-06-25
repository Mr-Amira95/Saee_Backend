<?php

namespace App\Models;

use App\Enums\DriverPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverPayment extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'period_start',
        'period_end',
        'basic_salary',
        'car_allowance',
        'order_count',
        'extra_orders_count',
        'extra_order_bonus',
        'gross_amount',
        'deductions',
        'net_amount',
        'payment_method',
        'reference_number',
        'status',
        'notes',
        'recorded_by',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start'  => 'date',
            'period_end'    => 'date',
            'basic_salary'  => 'decimal:2',
            'car_allowance' => 'decimal:2',
            'extra_order_bonus' => 'decimal:2',
            'gross_amount'  => 'decimal:2',
            'deductions'    => 'decimal:2',
            'net_amount'    => 'decimal:2',
            'status'        => DriverPaymentStatus::class,
            'approved_at'   => 'datetime',
            'paid_at'       => 'datetime',
        ];
    }

    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', DriverPaymentStatus::Draft);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', DriverPaymentStatus::Approved);
    }

    public function scopePaid($query)
    {
        return $query->where('status', DriverPaymentStatus::Paid);
    }
}
