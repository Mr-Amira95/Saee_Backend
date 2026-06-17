<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'code_hash',
        'reset_token_hash',
        'attempts',
        'verified_at',
        'used_at',
        'expires_at',
        'reset_token_expires_at',
    ];

    protected $casts = [
        'verified_at'            => 'datetime',
        'used_at'                => 'datetime',
        'expires_at'             => 'datetime',
        'reset_token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
