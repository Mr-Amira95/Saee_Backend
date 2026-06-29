<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging as FirebaseMessaging;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseMessaging::class, function ($app) {
            if ($app->bound('firebase.messaging')) {
                try {
                    return $app->make('firebase.messaging');
                } catch (\Throwable) {
                    return null;
                }
            }
            return null;
        });
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        $this->configureRateLimiters();

        // Share unread support tickets count with admin sidebar
        view()->composer('admin.partials.sidebar', function ($view) {
            if (auth()->check()) {
                $unreadCount = \App\Models\SupportMessage::where('is_read', false)
                    ->where(function($query) {
                        $query->whereNull('sender_id')
                              ->orWhereHas('sender', fn($sq) => $sq->whereNotIn('role', ['admin', 'superadmin']));
                    })
                    ->count();
                $view->with('unreadSupportTicketsCount', $unreadCount);
            }
        });

        // Share unread support messages count with client layout
        view()->composer('client.layouts.app', function ($view) {
            if (auth()->check()) {
                $userId = auth()->id();
                $unreadCount = \App\Models\SupportMessage::where('is_read', false)
                    ->whereHas('ticket', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->where('sender_id', '!=', $userId)
                    ->count();
                $view->with('unreadSupportMessagesCount', $unreadCount);
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
