<?php

namespace App\Models;

use App\Enums\DriverSalaryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class DriverSalaryConfig extends Model
{
    protected $fillable = [
        'driver_profile_id',
        'salary_type',
        'effective_from',
        'effective_to',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'salary_type'    => DriverSalaryType::class,
            'effective_from' => 'date',
            'effective_to'   => 'date',
        ];
    }

    public function driverProfile(): BelongsTo
    {
        return $this->belongsTo(DriverProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function perSalaryConfigs(): HasMany
    {
        return $this->hasMany(DriverPerSalaryConfig::class);
    }

    public function activePerSalaryConfig(): HasOne
    {
        return $this->hasOne(DriverPerSalaryConfig::class)->whereNull('effective_to');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('effective_to');
    }

    public function scopeActiveOn(Builder $query, Carbon $date): Builder
    {
        return $query
            ->where('effective_from', '<=', $date)
            ->where(fn (Builder $q) => $q
                ->whereNull('effective_to')
                ->orWhere('effective_to', '>=', $date)
            );
    }
}
