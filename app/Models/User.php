<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'phone_country_code',
        'otp_channel',
        'password',
        'role',
        'status',
        'notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'deleted_at'             => 'datetime',
            'notifications_enabled'  => 'boolean',
        ];
    }

    // Role helpers
    public function isSuperAdmin(): bool   { return $this->role === 'superadmin'; }
    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isDriver(): bool       { return $this->role === 'driver'; }
    public function isClientMaster(): bool { return $this->role === 'client_master'; }
    public function isClientEmployee(): bool { return $this->role === 'client_employee'; }

    /**
     * Page-level client-portal permission check. The client master always has
     * full access; a client_employee needs an explicit, non-expired grant.
     */
    public function hasClientPermission(string $page): bool
    {
        if ($this->isClientMaster()) {
            return true;
        }

        if (! $this->isClientEmployee()) {
            return false;
        }

        return DB::table('client_employee_permission_user')
            ->join('permissions', 'permissions.id', '=', 'client_employee_permission_user.permission_id')
            ->where('client_employee_permission_user.employee_user_id', $this->id)
            ->where('permissions.name', $page)
            ->where('permissions.scope', 'client')
            ->where(function ($q) {
                $q->whereNull('client_employee_permission_user.expires_at')
                    ->orWhere('client_employee_permission_user.expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Page names (e.g. "orders", "billing") this client account can access.
     * The client master gets every client-scope page; a client_employee gets
     * only their granted, non-expired pages. Empty for non-client roles.
     */
    public function clientPermissionNames(): array
    {
        if ($this->isClientMaster()) {
            return Permission::where('scope', 'client')->pluck('name')->all();
        }

        if (! $this->isClientEmployee()) {
            return [];
        }

        return DB::table('client_employee_permission_user')
            ->join('permissions', 'permissions.id', '=', 'client_employee_permission_user.permission_id')
            ->where('client_employee_permission_user.employee_user_id', $this->id)
            ->where('permissions.scope', 'client')
            ->where(function ($q) {
                $q->whereNull('client_employee_permission_user.expires_at')
                    ->orWhere('client_employee_permission_user.expires_at', '>', now());
            })
            ->pluck('permissions.name')
            ->all();
    }

    /**
     * All granted admin-scope permission names for this account (pages and
     * page.action rows), memoized for the lifetime of the request. Empty for
     * non-admin roles; superadmins never need this (they bypass checks below).
     */
    public function adminPermissionNames(): array
    {
        if (isset($this->adminPermissionNamesCache)) {
            return $this->adminPermissionNamesCache;
        }

        if (! $this->isAdmin()) {
            return $this->adminPermissionNamesCache = [];
        }

        return $this->adminPermissionNamesCache = DB::table('admin_permission_user')
            ->join('permissions', 'permissions.id', '=', 'admin_permission_user.permission_id')
            ->where('admin_permission_user.admin_user_id', $this->id)
            ->where('permissions.scope', 'admin')
            ->pluck('permissions.name')
            ->all();
    }

    /**
     * Page-level admin permission check, e.g. hasAdminPermission('clients').
     * Superadmins always pass; a plain admin needs the page permission itself.
     */
    public function hasAdminPermission(string $page): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($page, $this->adminPermissionNames(), true);
    }

    /**
     * Action-level admin permission check, e.g. hasAdminAction('clients.add').
     */
    public function hasAdminAction(string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($action, $this->adminPermissionNames(), true);
    }

    // Relationships
    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class, 'master_user_id');
    }



    public function clientEmployee(): HasOne
    {
        return $this->hasOne(ClientEmployee::class);
    }

    public function driverOrders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, DriverProfile::class);
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

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class, 'user_id');
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
