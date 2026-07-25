<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->prependToPriorityList(
            before: \Illuminate\Auth\Middleware\Authenticate::class,
            prepend: \App\Http\Middleware\SetLocale::class,
        );
        $middleware->alias([
            'admin.auth'    => \App\Http\Middleware\EnsureAdminRole::class,
            'admin.permission' => \App\Http\Middleware\EnsureAdminPagePermission::class,
            'admin.guest'   => \App\Http\Middleware\RedirectIfAdmin::class,
            'client.auth'   => \App\Http\Middleware\EnsureClientRole::class,
            'client.permission' => \App\Http\Middleware\EnsureClientPagePermission::class,
            'client.api.permission' => \App\Http\Middleware\EnsureApiClientPagePermission::class,
            'client.guest'  => \App\Http\Middleware\RedirectIfClient::class,
            'portal.guest'  => \App\Http\Middleware\RedirectIfPortalAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                \Illuminate\Support\Facades\App::setLocale(\App\Http\Middleware\SetLocale::detect($request));

                return response()->json([
                    'success' => false,
                    'message' => __('Unauthenticated'),
                    'code'    => 'UNAUTHENTICATED',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                \Illuminate\Support\Facades\App::setLocale(\App\Http\Middleware\SetLocale::detect($request));

                return response()->json([
                    'success' => false,
                    'message' => __('Too many requests. Please try again later.'),
                    'code'    => 'RATE_LIMITED',
                ], 429);
            }
        });
    })->create();
