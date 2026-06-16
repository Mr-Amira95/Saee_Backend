<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'deleted_at'        => 'datetime',
        ];
    }

    // Role helpers
    public function isSuperAdmin(): bool   { return $this->role === 'superadmin'; }
    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isDriver(): bool       { return $this->role === 'driver'; }
    public function isClientMaster(): bool { return $this->role === 'client_master'; }
    public function isClientEmployee(): bool { return $this->role === 'client_employee'; }

    // Relationships
    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class, 'master_user_id');
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function clientEmployee(): HasOne
    {
        return $this->hasOne(ClientEmployee::class);
    }

    public function driverOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    public function driverLedgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class, 'driver_id');
    }

    public function recordedLedgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class, 'recorded_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class)->orderBy('date', 'desc');
    }

    public function driverRatings(): HasMany
    {
        return $this->hasMany(DriverRating::class, 'driver_id');
    }

    public function getAverageRatingAttribute(): float
    {
        $avg = $this->driverRatings()->avg('rating');
        return $avg ? round($avg, 1) : 0.0;
    }

    public function getDeliverySuccessRateAttribute(): float
    {
        $total = $this->driverOrders()->count();
        if ($total === 0) {
            return 100.0;
        }
        $delivered = $this->driverOrders()->where('status', 'delivered')->count();
        return round(($delivered / $total) * 100, 1);
    }

    public function getAverageTransitHoursAttribute(): ?float
    {
        $orders = $this->driverOrders()
            ->where('status', 'delivered')
            ->get();

        if ($orders->isEmpty()) {
            return null;
        }

        $totalHours = 0;
        $counted = 0;

        foreach ($orders as $order) {
            $pickedUpLog = $order->trackingLogs
                ->where('to_status', 'picked_up')
                ->first();
            $deliveredLog = $order->trackingLogs
                ->where('to_status', 'delivered')
                ->first();

            if ($pickedUpLog && $deliveredLog) {
                $diffInMinutes = $pickedUpLog->created_at->diffInMinutes($deliveredLog->created_at);
                $totalHours += ($diffInMinutes / 60);
                $counted++;
            }
        }

        return $counted > 0 ? round($totalHours / $counted, 1) : null;
    }
}
