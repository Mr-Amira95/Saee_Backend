<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Support / notification / driver-location channels are public and need no authorization.

// Realtime data-change channels (App\Realtime\RealtimeHub) — private.
// Authorized at /broadcasting/auth (web session) and /api/broadcasting/auth (Sanctum token).

Broadcast::channel('admin.realtime', function (User $user) {
    return $user->isAdmin() || $user->isSuperAdmin();
});

Broadcast::channel('client.{clientProfileId}', function (User $user, $clientProfileId) {
    return (int) $user->resolveClientProfile()?->id === (int) $clientProfileId;
});

Broadcast::channel('driver.{userId}', function (User $user, $userId) {
    return $user->isDriver() && $user->id === (int) $userId;
});
