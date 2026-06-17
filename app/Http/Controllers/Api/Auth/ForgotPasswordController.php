<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPassword\RequestCodeRequest;
use App\Http\Requests\Api\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\Api\ForgotPassword\VerifyCodeRequest;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\SmsService;
use App\Traits\NormalizesPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use NormalizesPhone;

    public function __construct(private readonly SmsService $smsService) {}

    public function requestCode(RequestCodeRequest $request): JsonResponse
    {
        $candidates = $this->phoneCandidates(
            $request->phone_number,
            $request->country_code,
            $request->full_phone
        );

        $user = User::whereIn('phone', $candidates)->first();

        // Unknown phone or unsupported role — return generic success to prevent account enumeration
        if (! $user || in_array($user->role, ['admin', 'superadmin'])) {
            return $this->genericCodeSentResponse();
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'This account has been suspended',
                'code'    => 'ACCOUNT_SUSPENDED',
            ], 403);
        }

        if ($user->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This account is pending activation',
                'code'    => 'ACCOUNT_PENDING',
            ], 403);
        }

        // Expire all previous active codes for this user before issuing a new one
        PasswordResetCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        // Generate 6-digit code: str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)
        //$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $code = "123456"; // For testing purposes, use a fixed code. In production, uncomment the line above.
        PasswordResetCode::create([
            'user_id'    => $user->id,
            'phone'      => $user->phone,
            'code_hash'  => Hash::make($code),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->smsService->sendPasswordResetCode($user->phone, $code);

        $data = ['expires_in_seconds' => 300];

        // Expose the raw code only in local/development environments
        if (app()->environment('local', 'development')) {
            $data['debug_code'] = $code;
        }

        return response()->json([
            'success' => true,
            'message' => 'If the phone number is registered, a verification code has been sent.',
            'data'    => $data,
        ]);
    }

    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        $candidates = $this->phoneCandidates(
            $request->phone_number,
            $request->country_code,
            $request->full_phone
        );

        $user = User::whereIn('phone', $candidates)->first();

        if (! $user) {
            return $this->invalidCodeResponse();
        }

        $resetCode = PasswordResetCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (! $resetCode) {
            return $this->invalidCodeResponse();
        }

        if ($resetCode->attempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Too many invalid attempts. Please request a new code.',
                'code'    => 'TOO_MANY_ATTEMPTS',
            ], 429);
        }

        if (! Hash::check($request->code, $resetCode->code_hash)) {
            $resetCode->increment('attempts');

            return $this->invalidCodeResponse();
        }

        $resetToken = Str::random(64);

        $resetCode->update([
            'verified_at'            => now(),
            'reset_token_hash'       => hash('sha256', $resetToken),
            'reset_token_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Code verified successfully',
            'data'    => [
                'reset_token'        => $resetToken,
                'expires_in_seconds' => 600,
            ],
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $tokenHash = hash('sha256', $request->reset_token);

        $resetCode = PasswordResetCode::where('reset_token_hash', $tokenHash)
            ->whereNotNull('verified_at')
            ->whereNull('used_at')
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (! $resetCode) {
            return $this->invalidResetTokenResponse();
        }

        $user = User::find($resetCode->user_id);

        if (! $user) {
            return $this->invalidResetTokenResponse();
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'This account has been suspended',
                'code'    => 'ACCOUNT_SUSPENDED',
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active',
                'code'    => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        // The User model's 'hashed' cast handles bcrypt automatically
        $user->update(['password' => $request->password]);

        $resetCode->update(['used_at' => now()]);

        // Revoke all existing Sanctum tokens so the old session cannot be reused
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    private function genericCodeSentResponse(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'If the phone number is registered, a verification code has been sent.',
            'data'    => ['expires_in_seconds' => 300],
        ]);
    }

    private function invalidCodeResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code',
            'code'    => 'INVALID_OR_EXPIRED_CODE',
        ], 400);
    }

    private function invalidResetTokenResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token',
            'code'    => 'INVALID_OR_EXPIRED_RESET_TOKEN',
        ], 400);
    }
}
