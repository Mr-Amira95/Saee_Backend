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
            userId:     $ticket->user_id,
            title:      'New Support Ticket Opened',
            message:    'Operations has opened a support ticket: ' . $ticket->title,
            type:       'info',
            createdBy:  $createdBy,
            entityType: 'support_ticket',
            entityId:   $ticket->id,
        );
    }

    public function notifyAdminReply(SupportTicket $ticket, int $createdBy): void
    {
        $this->sendToUser(
            userId:     $ticket->user_id,
            title:      'New Message in Your Ticket',
            message:    'Operations replied to your ticket: ' . $ticket->title,
            type:       'info',
            createdBy:  $createdBy,
            entityType: 'support_ticket',
            entityId:   $ticket->id,
        );
    }

    // ── Driver → Admin ───────────────────────────────────────────────────────

    public function notifyAdminsNewTicket(SupportTicket $ticket): void
    {
        $this->sendToAdmins(
            title:      'New Support Ticket',
            message:    $ticket->user?->name . ' opened ticket: ' . $ticket->title,
            type:       'info',
            entityType: 'support_ticket',
            entityId:   $ticket->id,
        );
    }

    public function notifyAdminsDriverReply(SupportTicket $ticket): void
    {
        $this->sendToAdmins(
            title:      'New Message on Ticket ' . $ticket->ticket_number,
            message:    $ticket->user?->name . ' sent a message on: ' . $ticket->title,
            type:       'info',
            entityType: 'support_ticket',
            entityId:   $ticket->id,
        );
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function sendToUser(
        int $userId, string $title, string $message, string $type, int $createdBy,
        ?string $entityType = null, ?int $entityId = null,
    ): void {
        $record = SystemNotification::create([
            'user_id'     => $userId,
            'title'       => $title,
            'message'     => $message,
            'type'        => $type,
            'created_by'  => $createdBy,
            'link'        => $this->resolveLink($entityType, $entityId),
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
        ]);

        broadcast(new UserNotificationSent($userId, $title, $message, $type, $record->link, $entityType, $entityId));

        $tokens = UserDevice::where('user_id', $userId)
            ->where('notifications_enabled', true)
            ->pluck('fcm_token')
            ->all();

        if (! empty($tokens)) {
            $this->sendFcmPush($tokens, $title, $message, $type, $record->id, $entityType, $entityId);
        } else {
            $record->update(['fcm_status' => 'skipped']);
        }
    }

    private function sendToAdmins(
        string $title, string $message, string $type,
        ?string $entityType = null, ?int $entityId = null,
    ): void {
        $link = $this->resolveLink($entityType, $entityId);

        // One record per admin role — the admin bell query matches by role
        foreach (['admin', 'superadmin'] as $role) {
            SystemNotification::create([
                'user_id'     => null,
                'role'        => $role,
                'title'       => $title,
                'message'     => $message,
                'type'        => $type,
                'link'        => $link,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
            ]);
        }
    }

    private function resolveLink(?string $entityType, ?int $entityId): ?string
    {
        if (! $entityType || ! $entityId) {
            return null;
        }

        if ($entityType === 'support_ticket') {
            $ticket = SupportTicket::find($entityId);
            if ($ticket) {
                return route('admin.support.index') . '?ticket=' . $ticket->ticket_number;
            }
        }

        return null;
    }

    private function sendFcmPush(
        array $tokens, string $title, string $message, string $type,
        ?int $notificationId = null, ?string $entityType = null, ?int $entityId = null,
    ): void {
        $totalSent   = 0;
        $totalFailed = 0;
        $errorReasons = [];

        try {
            $messaging    = app(Messaging::class);
            $notification = Notification::create($title, $message);

            $data = array_filter([
                'type'        => $type,
                'entity_type' => $entityType,
                'entity_id'   => $entityId !== null ? (string) $entityId : null,
            ]);

            foreach (array_chunk($tokens, 500) as $chunk) {
                $multicast = CloudMessage::new()
                    ->withNotification($notification)
                    ->withData($data);

                $report = $messaging->sendMulticast($multicast, $chunk);

                $totalSent   += $report->successes()->count();
                $totalFailed += $report->failures()->count();

                if ($report->failures()->count() > 0) {
                    $dead = [];
                    foreach ($report->failures()->getItems() as $failure) {
                        $dead[] = $failure->target()->value();
                        if ($failure->error()) {
                            $errorReasons[] = $failure->error()->getMessage();
                        }
                    }
                    if (! empty($dead)) {
                        UserDevice::whereIn('fcm_token', $dead)->delete();
                    }
                }
            }

            if ($notificationId) {
                $status = match (true) {
                    $totalFailed === 0 => 'sent',
                    $totalSent   === 0 => 'failed',
                    default            => 'partial',
                };
                SystemNotification::where('id', $notificationId)->update([
                    'fcm_status'       => $status,
                    'fcm_sent_count'   => $totalSent,
                    'fcm_failed_count' => $totalFailed,
                    'fcm_error'        => $errorReasons
                        ? implode(' | ', array_unique($errorReasons))
                        : null,
                ]);
            }
        } catch (Throwable $e) {
            logger()->error('FCM push failed', [
                'error'  => $e->getMessage(),
                'tokens' => count($tokens),
            ]);

            if ($notificationId) {
                SystemNotification::where('id', $notificationId)->update([
                    'fcm_status'       => 'failed',
                    'fcm_sent_count'   => 0,
                    'fcm_failed_count' => count($tokens),
                    'fcm_error'        => $e->getMessage(),
                ]);
            }
        }
    }
}
