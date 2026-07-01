<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API equivalent of EnsureClientPagePermission — gates mobile-app client
 * endpoints behind the same page-level permissions used by the web portal.
 * The client_master is exempt; drivers are unaffected (not client_employee).
 */
class EnsureApiClientPagePermission
{
    protected array $pages = [
        'api.orders.'          => 'orders',
        'api.support.'         => 'support',
        'api.client.invoices.' => 'payout_invoices',
        'api.wallet.'          => 'payout_invoices',
        'api.client.billing.'  => 'billing',
        'api.reports.'         => 'reports',
        'api.users.'           => 'team',
        'api.bank-details.'    => 'account',
        'api.profile.'         => 'account',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isClientEmployee()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        foreach ($this->pages as $prefix => $page) {
            if (str_starts_with($routeName, $prefix)) {
                if (! $user->hasClientPermission($page)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('You do not have permission to access this resource.'),
                        'code'    => 'PERMISSION_DENIED',
                    ], 403);
                }
                break;
            }
        }

        return $next($request);
    }
}
