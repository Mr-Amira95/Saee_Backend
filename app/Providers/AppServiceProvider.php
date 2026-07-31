<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging as FirebaseMessaging;
use Kreait\Laravel\Firebase\FirebaseProjectManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseMessaging::class, function ($app) {
            try {
                return $app->make(FirebaseProjectManager::class)->project()->messaging();
            } catch (\Throwable $e) {
                logger()->error('[FCM DEBUG] Firebase Messaging binding failed to initialize — FCM sends will be skipped', [
                    'error_class'   => $e::class,
                    'error_message' => $e->getMessage(),
                    'credentials'   => env('FIREBASE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),
                ]);

                return null;
            }
        });
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        $this->configureRateLimiters();

        Paginator::defaultView('vendor.pagination.custom');

        // Share unread support tickets count with admin sidebar
        view()->composer('admin.partials.sidebar', function ($view) {
            if (auth()->check()) {
                $view->with('unreadSupportTicketsCount', \App\Models\SupportMessage::unreadForAdminCount());
            }
        });

        // Share unread support messages count with client layout
        view()->composer('client.layouts.app', function ($view) {
            if (auth()->check()) {
                $view->with('unreadSupportMessagesCount', \App\Models\SupportMessage::unreadForUserCount(auth()->id()));
            }
        });
    }

    private function configureRateLimiters(): void
    {
        // Forgot-password request-code: 3 attempts per phone per 10 min + 10 per IP per 10 min
        RateLimiter::for('forgot-password-request', function (Request $request) {
            return [
                Limit::perMinutes(10, 3)->by('phone:' . $request->input('phone_number', '')),
                Limit::perMinutes(10, 10)->by('ip:' . $request->ip()),
            ];
        });
    }
}
