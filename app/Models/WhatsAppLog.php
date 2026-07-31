<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppLog extends Model
{
    protected $table = 'whatsapp_logs';

    protected $fillable = [
        'order_id',
        'phone',
        'message',
        'status',
        'direction',
        'message_type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Canonical international-digits form of a phone number (e.g. "962792856567"),
     * used to group inbound/outbound logs into one conversation regardless of
     * which raw format (+962…, 0…, local 7-digit…) each side stored it in.
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '962')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '962' . substr($digits, 1);
        }

        return '962' . $digits;
    }
}
