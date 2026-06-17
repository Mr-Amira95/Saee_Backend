<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiters();
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
