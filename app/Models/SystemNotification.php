<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemNotification extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'title',
        'message',
        'link',
        'type',
        'read_at',
        'created_by',
        'fcm_status',
        'fcm_sent_count',
        'fcm_failed_count',
    ];

    protected function casts(): array
    {
        return [
            'read_at'          => 'datetime',
            'fcm_sent_count'   => 'integer',
            'fcm_failed_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
