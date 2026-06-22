<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WhatsAppLog;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Generic template dispatcher — the primary method for all outgoing messages.
     *
     * @param  string  $event      Template event key (e.g. 'order_picked_up').
     * @param  string  $phone      Recipient phone number.
     * @param  array   $variables  Map of placeholder names to replacement values.
     * @param  int|null $orderId   Optional order ID for audit logging.
     * @return array{success: bool, log?: WhatsAppLog, error?: string}
     */
    public function sendTemplate(
        string $event,
        string $phone,
        array  $variables = [],
        ?int   $orderId   = null,
    ): array {
        // 1. Validate phone
        if (empty(trim($phone))) {
            Log::warning("WhatsApp sendTemplate [{$event}]: missing phone number.");
            return ['success' => false, 'error' => 'Missing phone number.'];
        }

        // 2. Load template
        $template = WhatsAppTemplate::where('event', $event)->first();
        if (! $template) {
            Log::warning("WhatsApp sendTemplate [{$event}]: template not found in database.");
            return ['success' => false, 'error' => "Template [{$event}] not found."];
        }

        // 3. Replace {{placeholders}}
        $message = $this->replacePlaceholders($template->template_body, $variables);

        // 4. Send via provider
        $apiResult = $this->sendRawMessage($phone, $message);

        // 5. Log to database
        $status = $apiResult['success'] ? 'sent' : 'failed';
        $log = WhatsAppLog::create([
            'order_id' => $orderId,
            'phone'    => $phone,
            'message'  => $message,
            'status'   => $status,
        ]);

        if ($apiResult['success']) {
            Log::info("WhatsApp [{$event}] sent to {$phone}.", ['order_id' => $orderId]);
            return ['success' => true, 'log' => $log];
        }

        Log::error("WhatsApp [{$event}] failed to {$phone}: {$apiResult['error']}", [
            'order_id'   => $orderId,
            'api_status' => $apiResult['status'] ?? null,
        ]);

        return ['success' => false, 'error' => $apiResult['error'], 'log' => $log];
    }

    /**
     * Convenience wrapper that builds variables from an Order model.
     * Kept for backward compatibility; new callers should use sendTemplate() directly.
     */
    public function sendNotification(Order $order, string $event): ?WhatsAppLog
    {
        $variables = $this->buildOrderVariables($order, $event);
        $result    = $this->sendTemplate($event, $order->receiver_phone, $variables, $order->id);

        return $result['log'] ?? null;
    }

    /**
     * Send a plain text message to a phone number via the configured provider.
     *
     * @return array{success: bool, response?: array, error?: string, status?: int}
     */
    private function sendRawMessage(string $phone, string $message): array
    {
        $provider = config('whatsapp.provider', 'meta');

        try {
            return match ($provider) {
                'meta'  => $this->sendViaMeta($phone, $message),
                default => $this->sendViaMeta($phone, $message),
            };
        } catch (\Throwable $e) {
            Log::error("WhatsApp API exception: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a message using the Meta WhatsApp Cloud API.
     * Endpoint: POST {api_url}/{sender}/messages
     */
    private function sendViaMeta(string $phone, string $message): array
    {
        $url = sprintf('%s/%s/messages', rtrim(config('whatsapp.api_url'), '/'), config('whatsapp.sender'));

        $response = Http::withToken(config('whatsapp.api_token'))
            ->timeout(15)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => [
                    'preview_url' => false,
                    'body'        => $message,
                ],
            ]);

        if ($response->successful()) {
            return ['success' => true, 'response' => $response->json()];
        }

        $errorMessage = $response->json('error.message')
            ?? $response->json('error.error_data.details')
            ?? "HTTP {$response->status()}";

        return [
            'success' => false,
            'error'   => $errorMessage,
            'status'  => $response->status(),
        ];
    }

    /**
     * Replace all {{placeholder}} tokens in the template body.
     */
    private function replacePlaceholders(string $body, array $variables): string
    {
        $search  = array_map(fn ($k) => '{{' . $k . '}}', array_keys($variables));
        $replace = array_values($variables);

        return str_replace($search, $replace, $body);
    }

    /**
     * Build the standard variable map from an Order for legacy sendNotification() calls.
     */
    private function buildOrderVariables(Order $order, string $event): array
    {
        $locationLink = rescue(
            fn () => route('public.share-location', ['order_number' => $order->order_number]),
            ''
        );

        $rejectionReason = $event === 'order_rejected'
            ? (optional($order->rejectionReason)->reason ?? $order->notes ?? 'Not specified')
            : '';

        return [
            'customer_name'    => $order->receiver_name   ?? '',
            'order_number'     => $order->order_number    ?? '',
            'driver_name'      => optional($order->driver)->name  ?? '',
            'driver_phone'     => optional($order->driver)->phone ?? '',
            'location_link'    => $locationLink,
            'rejection_reason' => $rejectionReason,
        ];
    }
}
