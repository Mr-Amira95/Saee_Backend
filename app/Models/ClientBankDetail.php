<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientBankDetail extends Model
{
    protected $fillable = [
        'client_profile_id',
        'bank_name',
        'account_name',
        'account_number',
        'iban',
        'swift_code',
        'cliq_id',
        'cliq_alias_type',
        'notes',
    ];

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
