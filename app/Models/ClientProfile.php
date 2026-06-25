<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'master_user_id',
        'company_name',
        'company_name_ar',
        'commercial_register_number',
        'vat_number',
        'email',
        'company_phone',
        'company_phone_country_code',
        'logo_path',
        'address_line1',
        'city_id',
        'area_id',
        'credit_limit',
        'balance',
        'status',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit'                    => 'decimal:2',
            'balance'                         => 'decimal:2',
            'expiry_date'                     => 'date',
            'deleted_at'                      => 'datetime',
        ];
    }

    public function masterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_user_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Area::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(\App\Models\ClientAttachment::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(ClientEmployee::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ClientEmployeeInvitation::class);
    }

    public function deliveryPrices(): HasMany
    {
        return $this->hasMany(ClientDeliveryPrice::class);
    }

    public function bankDetail(): HasOne
    {
        return $this->hasOne(ClientBankDetail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function financialLedgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function deliveryInvoices(): HasMany
    {
        return $this->hasMany(ClientDeliveryInvoice::class);
    }

    public function getDeliveryPriceForCity(int $cityId): float
    {
        $custom = $this->deliveryPrices()->where('city_id', $cityId)->first();
        if ($custom) {
            return (float) $custom->delivery_price;
        }

        $city = City::find($cityId);
        return $city ? (float) $city->delivery_price : 0.0;
    }
}
