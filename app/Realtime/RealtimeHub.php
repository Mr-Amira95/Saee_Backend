<?php

namespace App\Realtime;

use App\Events\RealtimeChanged;
use App\Models\DriverProfile;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Collects model changes made during a request / job / command and broadcasts
 * them once at the end (after the HTTP response is sent) on private channels:
 *
 *   private-admin.realtime        every change (admins see everything)
 *   private-client.{profileId}    changes that belong to that client
 *   private-driver.{userId}       changes that belong to that driver
 *
 * Payloads only carry {type, id, action, by} — listeners refetch the data
 * through the normal (permission-checked) pages / API endpoints.
 */
class RealtimeHub
{
    public const ADMIN_CHANNEL = 'admin.realtime';

    /** Above this many changes per channel we send a compact "bulk" message (Pusher payload limit is 10KB). */
    private const MAX_CHANGES_PER_MESSAGE = 40;

    /** Flush early if a long-running process accumulates this many changes. */
    private const MAX_PENDING = 500;

    /** @var array<string, array<string, array{type: string, id: int|string|null, action: string, by: int|null}>> */
    private array $pending = [];

    private int $pendingCount = 0;

    /** @var array<int, int|null> driver_profile_id => user_id */
    private array $driverUserIds = [];

    /**
     * @param  array<int|null>  $clientProfileIds
     * @param  array<int|null>  $driverUserIds
     */
    public function record(string $type, int|string|null $id, string $action, array $clientProfileIds = [], array $driverUserIds = []): void
    {
        if (in_array(config('broadcasting.default'), [null, 'null'], true)) {
            return;
        }

        $channels = [self::ADMIN_CHANNEL];

        foreach (array_unique(array_filter($clientProfileIds)) as $clientProfileId) {
            $channels[] = 'client.' . $clientProfileId;
        }

        foreach (array_unique(array_filter($driverUserIds)) as $driverUserId) {
            $channels[] = 'driver.' . $driverUserId;
        }

        $change = [
            'type'   => $type,
            'id'     => $id,
            'action' => $action,
            'by'     => auth()->id(),
        ];

        // Runs immediately outside a transaction; waits for COMMIT (and is dropped on rollback) inside one.
        DB::afterCommit(function () use ($channels, $change) {
            foreach ($channels as $channel) {
                $this->queue($channel, $change);
            }
        });
    }

    public function flush(): void
    {
        $pending = $this->pending;
        $this->pending = [];
        $this->pendingCount = 0;

        foreach ($pending as $channel => $changes) {
            $changes = array_values($changes);
            $types   = array_values(array_unique(array_column($changes, 'type')));
            $bulk    = count($changes) > self::MAX_CHANGES_PER_MESSAGE;

            try {
                event(new RealtimeChanged($channel, $bulk ? [] : $changes, $types, $bulk));
            } catch (\Throwable $e) {
                logger()->warning('[Realtime] broadcast failed', [
                    'channel' => $channel,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    public function driverUserId(?int $driverProfileId): ?int
    {
        if (! $driverProfileId) {
            return null;
        }

        if (! array_key_exists($driverProfileId, $this->driverUserIds)) {
            $this->driverUserIds[$driverProfileId] = DriverProfile::withTrashed()
                ->whereKey($driverProfileId)
                ->value('user_id');
        }

        return $this->driverUserIds[$driverProfileId];
    }

    /**
     * Audience of anything hanging off an order (tracking logs, payment, receiver, rating...).
     *
     * @return array{clients: array<int|null>, drivers: array<int|null>}
     */
    public function orderAudience(?int $orderId): array
    {
        $order = $orderId
            ? Order::withTrashed()->select(['id', 'client_profile_id', 'driver_profile_id'])->find($orderId)
            : null;

        return [
            'clients' => [$order?->client_profile_id],
            'drivers' => [$this->driverUserId($order?->driver_profile_id)],
        ];
    }

    private function queue(string $channel, array $change): void
    {
        $key = $change['type'] . ':' . $change['id'];

        if (isset($this->pending[$channel][$key])) {
            $change['action'] = $this->mergeAction($this->pending[$channel][$key]['action'], $change['action']);
        } else {
            $this->pendingCount++;
        }

        $this->pending[$channel][$key] = $change;

        if ($this->pendingCount >= self::MAX_PENDING) {
            $this->flush();
        }
    }

    private function mergeAction(string $previous, string $next): string
    {
        if ($next === 'deleted') {
            return 'deleted';
        }

        return $previous === 'created' ? 'created' : $next;
    }
}
