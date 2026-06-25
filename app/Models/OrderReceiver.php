<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReceiver extends Model
{
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'order_id',
        'receiver_name',
        'receiver_phone',
        'city_id',
        'area_id',
        'address_text',
        'receiver_latitude',
        'receiver_longitude',
        'location_received_at',
    ];

    protected function casts(): array
    {
        return [
            'city_id'              => 'integer',
            'area_id'              => 'integer',
            'receiver_latitude'    => 'decimal:8',
            'receiver_longitude'   => 'decimal:8',
            'location_received_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
