<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientEmployee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_profile_id',
        'invitation_id',
        'job_title',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(ClientEmployeeInvitation::class);
    }
}
