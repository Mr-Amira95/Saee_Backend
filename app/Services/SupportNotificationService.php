<?php

namespace App\Services;

use App\Events\UserNotificationSent;
use App\Models\SystemNotification;
use App\Models\SupportTicket;
use App\Models\UserDevice;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class SupportNotificationService
{
    // ── Admin → Driver ───────────────────────────────────────────────────────

    public function notifyTicketOpened(SupportTicket $ticket, int $createdBy): void
    {
        $this->sendToUser(
            userId:    $ticket->user_id,
            title:     'New Support Ticket Opened',
            message:   'Operations has opened a support ticket: ' . $ticket->title,
            type:      'info',
            createdBy: $createdBy,
        );
    }

    public function notifyAdminReply(SupportTicket $ticket, int $createdBy): void
    {
        $this->sendToUser(
            userId:    $ticket->user_id,
            title:     'New Message in Your Ticket',
            message:   'Operations replied to your ticket: ' . $ticket->title,
            type:      'info',
            createdBy: $createdBy,
        );
    }

    // ── Driver → Admin ───────────────────────────────────────────────────────

    public function notifyAdminsNewTicket(SupportTicket $ticket): void
    {
        $this->sendToAdmins(
            title:   'New Support Ticket',
            message: $ticket->user?->name . ' opened ticket: ' . $ticket->title,
            type:    'info',
        );
    }

    public function notifyAdminsDriverReply(SupportTicket $ticket): void
    {
        $this->sendToAdmins(
            title:   'New Message on Ticket ' . $ticket->ticket_number,
            message: $ticket->user?->name . ' sent a message on: ' . $ticket->title,
            type:    'info',
        );
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function sendToUser(int $userId, string $title, string $message, string $type, int $createdBy): void
    {
        SystemNotification::create([
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'created_by' => $createdBy,
        ]);

        broadcast(new UserNotificationSent($userId, $title, $message, $type));

        $tokens = UserDevice::where('user_id', $userId)
            ->where('notifications_enabled', true)
            ->pluck('fcm_token')
            ->all();

        if (! empty($tokens)) {
            $this->sendFcmPush($tokens, $title, $message, $type);
        }
    }

    private function sendToAdmins(string $title, string $message, string $type): void
    {
        // One record per admin role — the admin bell query matches by role
        foreach (['admin', 'superadmin'] as $role) {
            SystemNotification::create([
                'user_id' => null,
                'role'    => $role,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
            ]);
        }
    }

    private function sendFcmPush(array $tokens, string $title, string $message, string $type): void
    {
        try {
            $messaging     = app(Messaging::class);
            $notification  = Notification::create($title, $message);

            foreach (array_chunk($tokens, 500) as $chunk) {
                $multicast = CloudMessage::new()
                    ->withNotification($notification)
                    ->withData(['type' => $type]);

                $report = $messaging->sendMulticast($multicast, $chunk);

                // Clean up tokens Firebase says are permanently invalid
                if ($report->failures()->count() > 0) {
                    $dead = [];
                    foreach ($report->failures()->getItems() as $failure) {
                        $dead[] = $failure->target()->value();
                    }
                    if (! empty($dead)) {
                        UserDevice::whereIn('fcm_token', $dead)->delete();
                    }
                }
            }
        } catch (Throwable $e) {
            logger()->error('FCM push failed', [
                'error'  => $e->getMessage(),
                'tokens' => count($tokens),
            ]);
        }
    }
}
