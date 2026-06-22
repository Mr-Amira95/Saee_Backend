<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $event,
        public readonly string $phone,
        public readonly array  $variables = [],
        public readonly ?int   $orderId   = null,
    ) {}

    public function handle(WhatsAppService $service): void
    {
        $result = $service->sendTemplate($this->event, $this->phone, $this->variables, $this->orderId);

        if (! $result['success']) {
            Log::warning('SendWhatsappMessageJob: send failed', [
                'event'    => $this->event,
                'phone'    => $this->phone,
                'order_id' => $this->orderId,
                'error'    => $result['error'] ?? 'Unknown',
                'attempt'  => $this->attempts(),
            ]);

            // Re-queue for retry if attempts remain
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsappMessageJob permanently failed', [
            'event'    => $this->event,
            'phone'    => $this->phone,
            'order_id' => $this->orderId,
            'error'    => $exception->getMessage(),
        ]);
    }
}
