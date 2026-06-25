<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'is_available',
        'current_latitude',
        'current_longitude',
        'location_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry_date'     => 'date',
            'car_license_expiry'      => 'date',
            'is_available'            => 'boolean',
            'location_updated_at'     => 'datetime',
            'deleted_at'              => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryConfigs(): HasMany
    {
        return $this->hasMany(DriverSalaryConfig::class);
    }

    public function activeSalaryConfig(): HasOne
    {
        return $this->hasOne(DriverSalaryConfig::class)->whereNull('effective_to');
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
