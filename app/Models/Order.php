<?php

namespace App\Models;

use App\Models\Concerns\BroadcastsRealtimeChanges;
use App\Realtime\RealtimeHub;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use BroadcastsRealtimeChanges;
    use SoftDeletes;

    /** How the delivery fee of a rejected order is settled (orders.rejection_fee_status). */
    public const REJECTION_FEE_NOT_CHARGED    = 'not_charged';    // client option off: nobody pays
    public const REJECTION_FEE_COLLECTED      = 'collected';      // customer paid the driver: credited to the client, minus Saee's fee
    public const REJECTION_FEE_CLIENT_CHARGED = 'client_charged'; // customer did not pay: the client is charged Saee's fee

    /** A rejected order on its way back to the client, before it is returned. */
    public const RETURN_FLOW_STATUSES = ['rejected', 'in_office', 'out_for_return'];

    protected $fillable = [
        'order_number',
        'batch_number',
        'client_profile_id',
        'driver_profile_id',
        'return_driver_profile_id',
        'handover_request_id',
        'client_statement_id',
        'order_description',
        'status',
        'payment_status',
        'route_order',
        'signature_path',
        'proof_image_path',
        'national_id_attachment_path',
        'delivery_latitude',
        'delivery_longitude',
        'rejection_reason_id',
        'rejection_fee_status',
        'cancellation_reason',
        'notes',
        'delivered_at',
        'returned_at',
        'return_confirmed_at',
        'return_confirmed_by',
        'delivery_shift',
    ];

    protected function casts(): array
    {
        return [
            'client_profile_id'   => 'integer',
            'driver_profile_id'   => 'integer',
            'return_driver_profile_id' => 'integer',
            'rejection_reason_id' => 'integer',
            'route_order'         => 'integer',
            'delivery_latitude'   => 'float',
            'delivery_longitude'  => 'float',
            'delivered_at'        => 'datetime',
            'returned_at'         => 'datetime',
            'return_confirmed_at' => 'datetime',
            'return_confirmed_by' => 'integer',
            'pickup_whatsapp_sent_at' => 'datetime',
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
        return $this->belongsTo(ClientProfile::class)->withTrashed();
    }

    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class)->withTrashed();
    }

    /** Driver taking a rejected order back to the client. */
    public function returnDriverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class, 'return_driver_profile_id')->withTrashed();
    }

    public function returnConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_confirmed_by');
    }

    /** Orders a driver delivers or has to take back to the client. */
    public function scopeForDriver(Builder $query, ?int $driverProfileId): Builder
    {
        if ($driverProfileId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(fn (Builder $q) => $q
            ->where('orders.driver_profile_id', $driverProfileId)
            ->orWhere('orders.return_driver_profile_id', $driverProfileId));
    }

    /** Returned to the client, who has not confirmed receiving it yet. */
    public function scopeAwaitingReturnConfirmation(Builder $query): Builder
    {
        return $query->where('orders.status', 'returned')->whereNull('orders.return_confirmed_at');
    }

    public function awaitsReturnConfirmation(): bool
    {
        return $this->status === 'returned' && $this->return_confirmed_at === null;
    }

    /**
     * Whether Saee's delivery fee is charged to the client. Always for a
     * delivered order; for a rejected / returned one unless the client's
     * rejected orders are free. When the customer paid the driver, that
     * amount is credited to the client (see collectedCustomerDeliveryFee()).
     */
    public function chargesClientDeliveryFee(): bool
    {
        return $this->status === 'delivered'
            || $this->rejection_fee_status !== self::REJECTION_FEE_NOT_CHARGED;
    }

    /** Delivery fee the customer pays the driver (0 when the client pays it). */
    public function customerDeliveryFee(): float
    {
        $payment = $this->payment;

        return $payment?->delivery_on_customer ? (float) ($payment->customer_delivery_amount ?? 0) : 0.0;
    }

    /**
     * Delivery fee the customer actually paid the driver, owed to the client:
     * on delivery, or on a rejected order whose fee the driver collected.
     */
    public function collectedCustomerDeliveryFee(): float
    {
        $collected = $this->status === 'delivered' || $this->rejection_fee_status === self::REJECTION_FEE_COLLECTED;

        return $collected ? $this->customerDeliveryFee() : 0.0;
    }

    /**
     * Cash the driver collected for this order: COD + customer delivery fee on
     * delivery, or only the delivery fee when a rejected order's fee was collected.
     */
    public function driverCashAmount(): float
    {
        $cod = $this->status === 'delivered' ? (float) ($this->payment?->order_amount ?? 0) : 0.0;

        return $cod + $this->collectedCustomerDeliveryFee();
    }

    public function handoverRequest(): BelongsTo
    {
        return $this->belongsTo(HandoverRequest::class);
    }

    public function clientStatement(): BelongsTo
    {
        return $this->belongsTo(ClientStatement::class);
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

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(WhatsAppLog::class)->orderBy('created_at');
    }

    public function realtimeAudience(): array
    {
        $hub = app(RealtimeHub::class);

        return [
            'clients' => $this->realtimeCurrentAndPrevious('client_profile_id'),
            'drivers' => array_map(fn ($id) => $hub->driverUserId($id), array_merge(
                $this->realtimeCurrentAndPrevious('driver_profile_id'),
                $this->realtimeCurrentAndPrevious('return_driver_profile_id'),
            )),
        ];
    }
}
