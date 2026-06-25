<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'order_id',
        'title',
        'status',
        'token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Auto-generate ticket number
            $latest = static::orderBy('id', 'desc')->first();
            $nextNumber = $latest ? ((int) str_replace('ST-', '', $latest->ticket_number)) + 1 : 10001;
            $ticket->ticket_number = 'ST-' . $nextNumber;

            // Auto-generate unique access token
            if (empty($ticket->token)) {
                $ticket->token = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at', 'asc');
    }
}
