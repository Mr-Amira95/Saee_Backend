<?php

namespace App\Services;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the "set your password" invitation to a new (or re-invited) user via
 * email or WhatsApp, and reports whether delivery actually succeeded so the
 * caller can tell the admin instead of always showing a success message.
 */
class UserInvitationService
{
    public function __construct(
        private readonly SetPasswordTokenService $tokens,
        private readonly WhatsAppService $whatsApp,
    ) {}

    /**
     * @return array{success: bool, channel: string, error?: string}
     */
    public function send(User $user, ?string $channel = null): array
    {
        $channel = ($channel ?? $user->otp_channel) === 'email' && $user->email ? 'email' : 'whatsapp';
        $token   = $this->tokens->issue($user);

        if ($channel === 'email') {
            try {
                Mail::to($user->email)->send(new UserInvitationMail($user, $token));
                return ['success' => true, 'channel' => $channel];
            } catch (\Throwable $e) {
                Log::error("Invitation email to user #{$user->id} failed: {$e->getMessage()}");
                return ['success' => false, 'channel' => $channel, 'error' => $e->getMessage()];
            }
        }

        $queryTail = '?token='.urlencode($token).'&email='.urlencode($user->email ?? '');
        $result = $this->whatsApp->sendTemplate('user_invitation', $user->whatsappPhone(), [
            'user_name' => $user->name,
        ], null, 'ar', [$queryTail]);

        return [
            'success' => $result['success'],
            'channel' => $channel,
            'error'   => $result['error'] ?? null,
        ];
    }

    /**
     * Flash-ready message describing a failed send.
     */
    public static function failureMessage(array $result): string
    {
        return __('The invitation could not be sent via :via: :error', [
            'via'   => $result['channel'] === 'email' ? 'email' : 'WhatsApp',
            'error' => $result['error'] ?? __('Unknown error'),
        ]);
    }
}
