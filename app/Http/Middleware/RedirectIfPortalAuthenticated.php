<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfPortalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $role = Auth::user()->role;

            if (\in_array($role, ['admin', 'superadmin'])) {
                return redirect()->route('admin.dashboard');
            }

            if (\in_array($role, ['client_master', 'client_employee'])) {
                return redirect()->route('client.dashboard');
            }
        }

        return $next($request);
    }
}
