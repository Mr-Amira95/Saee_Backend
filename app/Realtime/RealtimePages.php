<?php

namespace App\Realtime;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Decides whether / how the current dashboard page refreshes itself in realtime.
 * Rules live in config/realtime.php.
 */
class RealtimePages
{
    /**
     * Change types the page listens to, ['*'] for any, or null when the page must not auto-refresh.
     *
     * @return array<int, string>|null
     */
    public static function typesFor(?string $routeName): ?array
    {
        if (! $routeName || Str::is(config('realtime.disabled', []), $routeName)) {
            return null;
        }

        foreach (config('realtime.pages', []) as $pattern => $types) {
            if (Str::is($pattern, $routeName)) {
                return array_values(array_unique($types));
            }
        }

        return ['*'];
    }

    /** Private channel (without the "private-" prefix) the dashboard user listens on. */
    public static function channelFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return RealtimeHub::ADMIN_CHANNEL;
        }

        $clientProfileId = $user->resolveClientProfile()?->id;

        return $clientProfileId ? 'client.' . $clientProfileId : null;
    }
}
