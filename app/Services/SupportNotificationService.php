<?php

namespace App\Services;

use App\Events\UserNotificationSent;
use App\Models\Attendance;
use App\Models\Order;
use App\Models\SystemNotification;
use App\Models\SupportTicket;
use App\Models\UserDevice;
use App\Models\HandoverRequest;
use App\Models\Invoice;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class SupportNotificationService
{
    public function __construct(private readonly ?Messaging $messaging = null) {}

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

    public function notifyAdminsNewHandoverRequest(HandoverRequest $handoverRequest): void
    {
        $driverName = $handoverRequest->driver?->name ?? 'Driver';
        $this->sendToAdmins(
            title:      'New Checkout Handover Request',
            message:    "Driver {$driverName} has submitted a checkout handover request.",
            type:       'info',
            entityType: 'handover_request',
            entityId:   $handoverRequest->id,
        );
    }

    // ── Client → Admin: Order Notifications ─────────────────────────────────

    public function notifyAdminsNewOrder(Order $order): void
    {
        $company = $order->clientProfile?->company_name ?? 'Client';
        $this->sendToAdmins(
            title:      'New Order Created',
            message:    "{$company} created order #{$order->order_number}",
            type:       'info',
            entityType: 'order',
            entityId:   $order->id,
        );
    }

    public function notifyAdminsCancelOrder(Order $order): void
    {
        $company = $order->clientProfile?->company_name ?? 'Client';
        $this->sendToAdmins(
            title:      'Order Cancelled',
            message:    "{$company} cancelled order #{$order->order_number}",
            type:       'warning',
            entityType: 'order',
            entityId:   $order->id,
        );
    }

    public function notifyAdminsOrdersImported(string $company, int $count, string $batchNumber): void
    {
        $this->sendToAdmins(
            title:   'Orders Imported',
            message: "{$company} imported {$count} order(s) — Batch: {$batchNumber}",
            type:    'info',
        );
    }

    // ── Admin → Client: Ticket Resolved ─────────────────────────────────────

    public function notifyClientTicketResolved(SupportTicket $ticket, int $createdBy): void
    {
        $this->sendToUser(
            userId:     $ticket->user_id,
            title:      'Support Ticket Resolved',
            message:    'Your support ticket has been resolved: ' . $ticket->title,
            type:       'info',
            createdBy:  $createdBy,
            entityType: 'support_ticket',
            entityId:   $ticket->id,
        );
    }

    // ── Order Assignment Notifications ───────────────────────────────────────

    public function notifyOrdersAssigned(int $driverId, array $orderIds, int $createdBy): void
    {
        $hasCheckedIn = Attendance::where('user_id', $driverId)
            ->whereDate('date', today())
            ->whereNotNull('check_in_at')
            ->exists();

        if ($hasCheckedIn) {
            if (count($orderIds) === 1) {
                $this->sendToUser(
                    userId:     $driverId,
                    title:      'New Order Assigned',
                    message:    'You have a new order assigned to you.',
                    type:       'info',
                    createdBy:  $createdBy,
                    entityType: 'single_order',
                    entityId:   $orderIds[0],
                );
            } else {
                $this->sendToUser(
                    userId:     $driverId,
                    title:      'New Orders Assigned',
                    message:    'You have ' . count($orderIds) . ' new orders assigned to you.',
                    type:       'info',
                    createdBy:  $createdBy,
                    entityType: 'batch_order',
                    entityId:   null,
                );
            }
        } else {
            $this->sendToUser(
                userId:     $driverId,
                title:      'New Orders Waiting',
                message:    'You have new orders assigned. Check in to view them.',
                type:       'info',
                createdBy:  $createdBy,
                entityType: 'attendance',
                entityId:   null,
            );
        }
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

        if (! empty($tokens) && $this->messaging !== null) {
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

    public function notifyClientOrderStatusChanged(Order $order, string $status, int $actorId): void
    {
        $clientProfile = $order->clientProfile;
        if (! $clientProfile) {
            return;
        }

        $userIds = [$clientProfile->master_user_id];
        $employeeUserIds = $clientProfile->employees()->pluck('user_id')->toArray();
        $userIds = array_merge($userIds, $employeeUserIds);

        $title = $status === 'delivered' ? 'Order Delivered' : 'Order Rejected';
        $message = $status === 'delivered'
            ? "Your order #{$order->order_number} has been delivered."
            : "Your order #{$order->order_number} has been rejected.";

        foreach (array_unique(array_filter($userIds)) as $userId) {
            $this->sendToUser(
                userId:     $userId,
                title:      $title,
                message:    $message,
                type:       'info',
                createdBy:  $actorId,
                entityType: 'single_order',
                entityId:   $order->id,
            );
        }
    }

    public function notifyClientPayoutMade(Invoice $invoice, int $actorId): void
    {
        $client = $invoice->clientProfile;
        if (! $client) {
            return;
        }

        $userIds = [$client->master_user_id];
        $employeeUserIds = $client->employees()->pluck('user_id')->toArray();
        $userIds = array_merge($userIds, $employeeUserIds);

        $title = 'COD Payout Received';
        $message = "A COD payout of " . number_format($invoice->net_amount, 2) . " JD has been processed. Invoice: {$invoice->invoice_number}.";

        foreach (array_unique(array_filter($userIds)) as $userId) {
            $this->sendToUser(
                userId:     $userId,
                title:      $title,
                message:    $message,
                type:       'info',
                createdBy:  $actorId,
                entityType: 'client_payout',
                entityId:   $invoice->id,
            );
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

        if ($entityType === 'order') {
            return route('admin.orders.show', $entityId);
        }

        if ($entityType === 'handover_request') {
            return route('admin.financials.handover-requests.show', $entityId);
        }

        if ($entityType === 'single_order') {
            return route('client.orders.show', $entityId);
        }

        if ($entityType === 'client_payout') {
            return route('client.financials.invoices.show', $entityId);
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

                $report = $this->messaging->sendMulticast($multicast, $chunk);

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
