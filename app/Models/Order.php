<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'driver_profile_id',
        'handover_request_id',
        'order_description',
        'status',
        'payment_status',
        'route_order',
        'signature_path',
        'proof_image_path',
        'national_id_attachment_path',
        'rejection_reason_id',
        'notes',
        'delivered_at',
        'returned_at',
        'delivery_shift',
    ];

    protected function casts(): array
    {
        return [
            'client_profile_id'   => 'integer',
            'driver_profile_id'   => 'integer',
            'rejection_reason_id' => 'integer',
            'route_order'         => 'integer',
            'delivered_at'        => 'datetime',
            'returned_at'         => 'datetime',
            'deleted_at'          => 'datetime',
            'delivery_shift'      => \App\Enums\DeliveryShift::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $clientIdStr = sprintf('%02d', $order->client_profile_id);
                $dateStr = now()->format('ymd');
                $prefix = $clientIdStr . $dateStr;

                $latestOrder = static::where('order_number', 'like', "{$prefix}%")
                    ->orderBy('order_number', 'desc')
                    ->first();

                if ($latestOrder) {
                    $sequence = intval(substr($latestOrder->order_number, -4)) + 1;
                } else {
                    $sequence = 1;
                }

                $sequence = ($sequence - 1) % 9999 + 1;
                $sequenceStr = sprintf('%04d', $sequence);

                $order->order_number = $prefix . $sequenceStr;
            }
        });
    }

    // Returns the driver User model — used by blade views and legacy code
    public function getDriverAttribute(): ?User
    {
        return $this->driverProfile?->user;
    }

    public function getDriverIdAttribute(): ?int
    {
        return $this->driverProfile?->user_id;
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function handoverRequest(): BelongsTo
    {
        return $this->belongsTo(HandoverRequest::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(OrderPayment::class, 'order_id');
    }

    public function receiver(): HasOne
    {
        return $this->hasOne(OrderReceiver::class, 'order_id');
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

    public function clientDeliveryInvoice(): BelongsToMany
    {
        return $this->belongsToMany(ClientDeliveryInvoice::class, 'client_delivery_invoice_orders');
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
