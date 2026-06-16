<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientEmployeeInvitation extends Model
{
    protected $fillable = [
        'client_profile_id',
        'invited_by_user_id',
        'email',
        'token',
        'permissions',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'expires_at'  => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(ClientEmployee::class, 'invitation_id');
    }
}
