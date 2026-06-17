<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Api\ClientProfileResource;
use App\Http\Resources\Api\DriverProfileResource;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $candidates = $this->phoneCandidates(
            $request->phone_number,
            $request->country_code,
            $request->full_phone
        );

        $user = User::whereIn('phone', $candidates)->first();

        if (! $user) {
            return $this->invalidCredentials();
        }

        if (! Hash::check($request->password, $user->password)) {
            return $this->invalidCredentials();
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

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active',
                'code'    => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        if (in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'This account type is not supported in the mobile app',
                'code'    => 'UNSUPPORTED_ACCOUNT_TYPE',
            ], 403);
        }

        [$roleType, $profileResource] = $this->resolveProfile($user);

        if ($profileResource === null) {
            return response()->json([
                'success' => false,
                'message' => 'User profile was not found',
                'code'    => 'PROFILE_NOT_FOUND',
            ], 404);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'data'    => [
                'token'     => $token,
                'role_type' => $roleType,
                'user'      => new UserResource($user),
                'profile'   => $profileResource,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
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

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => ['current_password' => ['Current password is incorrect.']],
                'code'    => 'CURRENT_PASSWORD_INCORRECT',
            ], 422);
        }

        $user->update(['password' => $request->password]);

        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken) {
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    private function resolveProfile(User $user): array
    {
        if ($user->isDriver()) {
            $profile = $user->driverProfile;
            return ['driver', $profile ? new DriverProfileResource($profile) : null];
        }

        if ($user->isClientMaster()) {
            $profile = $user->clientProfile;
            return ['client', $profile ? new ClientProfileResource($profile) : null];
        }

        if ($user->isClientEmployee()) {
            $profile = $user->clientEmployee?->clientProfile;
            return ['client', $profile ? new ClientProfileResource($profile) : null];
        }

        // admin/superadmin are rejected before this point; unknown roles have no profile
        return ['unknown', null];
    }

    // Generates phone candidates from the three possible inputs to match any stored format.
    private function phoneCandidates(string $phoneNumber, ?string $countryCode, ?string $fullPhone): array
    {
        $phoneDigits   = preg_replace('/\D/', '', $phoneNumber);
        $countryDigits = preg_replace('/\D/', '', $countryCode ?? '');
        $fullDigits    = preg_replace('/\D/', '', $fullPhone ?? '');

        $candidates = [];

        // full_phone as-is and normalised with leading zero
        if ($fullPhone) {
            $candidates[] = $fullPhone;
            $candidates[] = '0' . ltrim($fullDigits, '0');
        }

        // phone_number raw and with leading zero prefix
        if ($phoneDigits) {
            $candidates[] = $phoneDigits;
            $candidates[] = '0' . $phoneDigits;
        }

        // country-code + phone_number variants
        if ($countryDigits && $phoneDigits) {
            $candidates[] = $countryDigits . $phoneDigits;
            $candidates[] = '+' . $countryDigits . $phoneDigits;
        }

        return array_unique(array_filter($candidates));
    }

    private function invalidCredentials(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Invalid phone number or password',
        ], 401);
    }
}
