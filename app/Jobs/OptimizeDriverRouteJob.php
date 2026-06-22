<?php

namespace App\Jobs;

use App\Services\RouteOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizeDriverRouteJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60];

    public function __construct(public readonly int $driverId) {}

    /**
     * Unique key per driver — only one optimization can be queued at a time per driver.
     */
    public function uniqueId(): string
    {
        return "driver_route_{$this->driverId}";
    }

    /**
     * Hold the unique lock for 120s:
     * - Exceeds queue retry_after (90s) so the lock doesn't expire before the job runs.
     * - Exceeds the 60s dispatch delay, so the first dispatch holds the lock while
     *   it waits in the queue, silently dropping duplicate dispatches.
     */
    public function uniqueFor(): int
    {
        return 120;
    }

    public function handle(RouteOptimizationService $service): void
    {
        Log::info('OptimizeDriverRouteJob: starting', ['driver_id' => $this->driverId]);

        $result = $service->optimize($this->driverId);

        // Cache the result for 5 minutes so GET /driver/route reads from cache
        // instead of re-querying and re-sorting DB records on every request.
        Cache::put("driver_route_{$this->driverId}", $result, now()->addMinutes(5));

        Log::info('OptimizeDriverRouteJob: completed', [
            'driver_id'      => $this->driverId,
            'order_count'    => count($result['orders']),
            'total_duration' => $result['total_duration_s'],
            'chunks'         => $result['chunks'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('OptimizeDriverRouteJob permanently failed', [
            'driver_id' => $this->driverId,
            'error'     => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
