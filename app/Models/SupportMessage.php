<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'sender_id',
        'sender_name',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'sender_id' => 'integer',
            'is_read'   => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public static function unreadForAdminCount(): int
    {
        return static::where('is_read', false)
            ->where(function ($query) {
                $query->whereNull('sender_id')
                      ->orWhereHas('sender', fn ($sq) => $sq->whereNotIn('role', ['admin', 'superadmin']));
            })
            ->count();
    }

    public static function unreadForUserCount(int $userId): int
    {
        return static::where('is_read', false)
            ->whereHas('ticket', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('sender_id', '!=', $userId)
            ->count();
    }
}
