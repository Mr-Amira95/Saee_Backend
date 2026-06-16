<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class City extends Model
{
    protected $fillable = ['name', 'name_ar', 'country_code', 'is_active', 'delivery_price'];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'delivery_price' => 'decimal:2',
        ];
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function clientDeliveryPrices(): HasMany
    {
        return $this->hasMany(ClientDeliveryPrice::class);
    }

    public function orderRates(): HasMany
    {
        return $this->hasMany(CityOrderRate::class);
    }

    public function activeOrderRate(): HasOne
    {
        return $this->hasOne(CityOrderRate::class)->whereNull('effective_to');
    }
}
