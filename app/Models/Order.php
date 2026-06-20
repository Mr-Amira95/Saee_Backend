<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'batch_number',
        'client_profile_id',
        'driver_id',
        'order_description',
        'payment_type',
        'delivery_on_customer',
        'delivery_customer_amount',
        'delivery_amount',
        'order_price',
        'receiver_name',
        'receiver_phone',
        'city_id',
        'area_id',
        'address_text',
        'address_location',
        'status',
        'payment_status',
        'signature_path',
        'proof_image_path',
        'rejection_reason_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'client_profile_id'        => 'integer',
            'driver_id'                => 'integer',
            'city_id'                  => 'integer',
            'area_id'                  => 'integer',
            'rejection_reason_id'      => 'integer',
            'delivery_on_customer'     => 'boolean',
            'delivery_customer_amount' => 'decimal:2',
            'delivery_amount'          => 'decimal:2',
            'order_price'              => 'decimal:2',
            'deleted_at'               => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $clientIdStr = sprintf('%02d', $order->client_profile_id);
                $dateStr = now()->format('ymd');
                $prefix = $clientIdStr . $dateStr;

                // Find the latest order for this client today to determine sequence
                $latestOrder = static::where('order_number', 'like', "{$prefix}%")
                    ->orderBy('order_number', 'desc')
                    ->first();

                if ($latestOrder) {
                    $sequence = intval(substr($latestOrder->order_number, -4)) + 1;
                } else {
                    $sequence = 1;
                }

                $sequence = ($sequence - 1) % 9999 + 1; // Wrap sequence to 1-9999
                $sequenceStr = sprintf('%04d', $sequence);

                $order->order_number = $prefix . $sequenceStr;
            }
        });
    }

    // Relationships
    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(OrderTrackingLog::class)->orderBy('created_at', 'desc');
    }

    public function financialLedgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class)->orderBy('created_at', 'asc');
    }

    public function driverRating(): HasOne
    {
        return $this->hasOne(DriverRating::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}
