<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates client-portal pages behind the page-level permissions assigned to a
 * client_employee. The client_master is exempt (always has full access).
 * Routes not listed in $pages (dashboard, notifications, logout, ...) are
 * always allowed once authenticated.
 */
class EnsureClientPagePermission
{
    protected array $pages = [
        'client.orders.'      => 'orders',
        'client.support.'     => 'support',
        'client.financials.'  => 'payout_invoices',
        'client.billing.'     => 'billing',
        'client.reports.'     => 'reports',
        'client.users.'       => 'team',
        'client.account.'     => 'account',
        'client.ai-chat.'     => 'ai_assistant',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isClientEmployee()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        foreach ($this->pages as $prefix => $page) {
            if (str_starts_with($routeName, $prefix)) {
                if (! $user->hasClientPermission($page)) {
                    abort(403, __('You do not have permission to access this page.'));
                }
                break;
            }
        }

        return $next($request);
    }
}
