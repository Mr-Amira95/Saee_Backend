<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DriverPerSalaryConfig extends Model
{
    protected $fillable = [
        'driver_salary_config_id',
        'basic_salary',
        'car_allowance',
        'extra_order_threshold',
        'extra_order_bonus',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary'          => 'decimal:2',
            'car_allowance'         => 'decimal:2',
            'extra_order_threshold' => 'integer',
            'extra_order_bonus'     => 'decimal:2',
            'effective_from'        => 'date',
            'effective_to'          => 'date',
        ];
    }

    public function driverSalaryConfig(): BelongsTo
    {
        return $this->belongsTo(DriverSalaryConfig::class);
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
