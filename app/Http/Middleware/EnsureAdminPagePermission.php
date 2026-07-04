<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates admin-panel pages behind the page-level permissions assigned to a
 * plain admin. The superadmin is exempt (always has full access). Routes not
 * listed in $pages (dashboard, logout, ...) are always allowed once authenticated.
 */
class EnsureAdminPagePermission
{
    protected array $pages = [
        'admin.clients.'           => 'clients',
        'admin.drivers.'           => 'drivers',
        'admin.admins.'            => 'admins',
        'admin.orders.'            => 'orders',
        'admin.support.'           => 'support',
        'admin.ai-conversations.'  => 'ai_conversations',
        'admin.reports.'          => 'reports',
        'admin.cities.'            => 'cities',
        'admin.rejection-reasons.' => 'rejection_reasons',
        'admin.financials.'       => 'finances',
        'admin.payroll.'          => 'finances',
        'admin.billing.'          => 'finances',
        'admin.expenses.'         => 'finances',
        'admin.cms.'               => 'cms',
        'admin.settings.site.'    => 'cms',
        'admin.settings.legal.'   => 'cms',
        'admin.attendance.'        => 'attendance',
        'admin.notifications.'    => 'notifications',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        foreach ($this->pages as $prefix => $page) {
            if (str_starts_with($routeName, $prefix)) {
                if (! $user->hasAdminPermission($page)) {
                    abort(403, __('You do not have permission to access this page.'));
                }
                break;
            }
        }

        return $next($request);
    }
}
