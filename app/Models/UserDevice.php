<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'fcm_token',
        'platform',
        'app_version',
        'notifications_enabled',
    ];

    protected function casts(): array
    {
        return [
            'notifications_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
