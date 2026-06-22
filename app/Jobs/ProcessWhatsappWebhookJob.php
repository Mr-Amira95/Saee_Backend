<?php

namespace App\Jobs;

use App\Services\WhatsappWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsappWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(public readonly array $payload) {}

    public function handle(WhatsappWebhookService $service): void
    {
        $service->handleIncomingMessage($this->payload);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessWhatsappWebhookJob permanently failed', [
            'error'   => $exception->getMessage(),
            'payload' => $this->payload,
        ]);
    }
}
