<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

                $sequence = static::nextSequenceFor($prefix);
                $sequence = ($sequence - 1) % 9999 + 1;
                $sequenceStr = sprintf('%04d', $sequence);

                $order->order_number = $prefix . $sequenceStr;
            }
        });
    }

    /**
     * Atomically reserve the next order-number sequence for a given prefix.
     *
     * Uses a locking read on a dedicated counter row (rather than
     * MAX(order_number)+1 on the orders table) so that concurrent order
     * creations for the same client/day can't read the same "next" value
     * and collide on the order_number unique constraint.
     */
    protected static function nextSequenceFor(string $prefix): int
    {
        return DB::transaction(function () use ($prefix) {
            $counter = DB::table('order_number_counters')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            $next = $counter->next_sequence ?? 1;

            DB::table('order_number_counters')->updateOrInsert(
                ['prefix' => $prefix],
                ['next_sequence' => $next + 1]
            );

            return $next;
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
