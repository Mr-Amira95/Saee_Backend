<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send a password-reset OTP to the given phone number.
     *
     * TODO: Replace the Log stub below with a real SMS provider
     *       (e.g. Unifonic, Twilio, Msegat, etc.).
     *
     * Message templates:
     *   EN: "Your SAEE password reset code is {code}. It expires in 5 minutes."
     *   AR: "رمز إعادة تعيين كلمة المرور في ساعي هو {code}. ينتهي خلال 5 دقائق."
     */
    public function sendPasswordResetCode(string $phone, string $code): void
    {
        // TODO: integrate SMS provider here
        Log::info('SMS [password-reset]', ['phone' => $phone, 'code' => $code]);
    }
}
