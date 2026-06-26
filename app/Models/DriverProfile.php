<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DriverProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'national_id',
        'license_number',
        'license_expiry_date',
        'license_attachment',
        'vehicle_type',
        'vehicle_plate',
        'car_license_expiry',
        'car_license_attachment',
        'avatar_path',
        'basic_salary',
        'car_allowance',
        'daily_order_threshold',
        'bonus_per_extra_order',
        'is_available',
        'current_latitude',
        'current_longitude',
        'location_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id'                 => 'integer',
            'license_expiry_date'     => 'date',
            'car_license_expiry'      => 'date',
            'basic_salary'            => 'decimal:2',
            'car_allowance'           => 'decimal:2',
            'daily_order_threshold'   => 'integer',
            'bonus_per_extra_order'   => 'decimal:2',
            'is_available'            => 'boolean',
            'location_updated_at'     => 'datetime',
            'deleted_at'              => 'datetime',
        ];
    }

    public function bankDetail(): HasOne
    {
        return $this->hasOne(DriverBankDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DriverPayment::class);
    }

    public function locationHistories(): HasMany
    {
        return $this->hasMany(DriverLocationHistory::class);
    }

    public function locationHistoriesBetween(Carbon $from, Carbon $to): HasMany
    {
        return $this->locationHistories()
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at');
    }
}
