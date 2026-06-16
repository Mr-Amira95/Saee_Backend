<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDeliveryPrice extends Model
{
    protected $fillable = ['client_profile_id', 'city_id', 'delivery_price'];

    protected function casts(): array
    {
        return ['delivery_price' => 'decimal:2'];
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
