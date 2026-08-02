<?php

namespace App\Services;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Issues and resolves "set password" links (account invitations and
 * OTP-verified password resets) without depending on the user having an
 * email address. Laravel's built-in Password broker keys everything off
 * email via the password_reset_tokens table, whose email column is a
 * NOT NULL primary key — that breaks for whatsapp-only accounts.
 */
class SetPasswordTokenService
{
    public function issue(User $user, int $validDays = 7): string
    {
        $token = Str::random(64);

        PasswordResetCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        PasswordResetCode::create([
            'user_id'                => $user->id,
            'phone'                  => $user->phone ?? '',
            'code_hash'              => Hash::make(Str::random(6)),
            'reset_token_hash'       => hash('sha256', $token),
            'verified_at'            => now(),
            'expires_at'             => now()->addDays($validDays),
            'reset_token_expires_at' => now()->addDays($validDays),
        ]);

        return $token;
    }

    public function resolve(string $token): ?User
    {
        $record = PasswordResetCode::where('reset_token_hash', hash('sha256', $token))
            ->whereNotNull('verified_at')
            ->whereNull('used_at')
            ->where('reset_token_expires_at', '>', now())
            ->first();

        return $record ? User::find($record->user_id) : null;
    }

    public function consume(string $token): void
    {
        PasswordResetCode::where('reset_token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }
}
