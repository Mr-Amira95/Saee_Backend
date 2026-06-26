<?php

namespace App\Models;

use App\Enums\DriverPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverPayment extends Model
{
    use SoftDeletes;

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
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start'      => 'date',
            'period_end'        => 'date',
            'basic_salary'      => 'decimal:2',
            'car_allowance'     => 'decimal:2',
            'extra_order_bonus' => 'decimal:2',
            'gross_amount'      => 'decimal:2',
            'deductions'        => 'decimal:2',
            'net_amount'        => 'decimal:2',
            'status'            => DriverPaymentStatus::class,
            'paid_at'           => 'datetime',
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

    public function scopeDraft($query)
    {
        return $query->where('status', DriverPaymentStatus::Draft);
    }

    public function scopePaid($query)
    {
        return $query->where('status', DriverPaymentStatus::Paid);
    }
}
