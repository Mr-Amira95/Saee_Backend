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
     * @param  string  $languageCode  Language code (default: 'en_US').
     * @return array{success: bool, log?: WhatsAppLog, error?: string}
     */
    public function sendTemplate(
        string $event,
        string $phone,
        array  $variables = [],
        ?int   $orderId   = null,
        string $languageCode = 'en_US'
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

        // 3. Normalise phone to international format required by Meta (e.g. 962792856567)
        $normalizedPhone = $this->normalizePhone($phone);

        // 4. Send via provider
        $provider = config('whatsapp.provider', 'meta');

        if ($provider === 'meta') {
            // Parse placeholders from the template body in order to match Meta's positional parameters ({{1}}, {{2}}, etc.)
            preg_match_all('/\{\{([^}]+)\}\}/', $template->template_body, $matches);
            $placeholderNames = $matches[1] ?? [];

            $parameters = [];
            foreach ($placeholderNames as $name) {
                $parameters[] = $variables[trim($name)] ?? '';
            }

            return $this->sendStructuredTemplate(
                $normalizedPhone,
                $event, // Meta template name must match event name
                $languageCode,
                $parameters,
                $orderId
            );
        }

        // Fallback for non-meta providers
        $message = $this->replacePlaceholders($template->template_body, $variables);
        $apiResult = $this->sendRawMessage($normalizedPhone, $message);

        // Log to database
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
        $result    = $this->sendTemplate($event, $order->receiver?->receiver_phone ?? '', $variables, $order->id);

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
            Log::info('WhatsApp Meta API response', ['body' => $response->json()]);
            return ['success' => true, 'response' => $response->json()];
        }

        // Log the full error body so we can diagnose delivery issues
        Log::error('WhatsApp Meta API error', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

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
     * Normalise a phone number to the format Meta expects: digits only, with country code, no leading +.
     * Examples:
     *   0792856567   → 962792856567  (Jordanian local → international)
     *   +962792856567 → 962792856567
     *   962792856567  → 962792856567  (already correct)
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Already has country code (Jordan 962, length 12)
        if (str_starts_with($digits, '962')) {
            return $digits;
        }

        // Local format: leading 0 → strip and prepend 962
        if (str_starts_with($digits, '0')) {
            return '962' . substr($digits, 1);
        }

        // No leading 0 and no country code — assume Jordan
        return '962' . $digits;
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
            'customer_name'    => $order->receiver?->receiver_name   ?? '',
            'order_number'     => $order->order_number              ?? '',
            'driver_name'      => $order->driverProfile?->user?->name  ?? '',
            'driver_phone'     => $order->driverProfile?->user?->phone ?? '',
            'location_link'    => $locationLink,
            'rejection_reason' => $rejectionReason,
        ];
    }

    /**
     * Send a structured template message using the Meta WhatsApp Cloud API.
     * This bypasses the 24-hour customer window constraint.
     *
     * @param  string  $phone         Recipient phone number.
     * @param  string  $templateName  Official Meta template name (e.g. 'hello_world').
     * @param  string  $languageCode  Language code (e.g. 'en_US').
     * @param  array   $parameters    Positional array of parameters for the template body.
     * @param  int|null $orderId      Optional order ID for auditing.
     * @return array{success: bool, log?: WhatsAppLog, error?: string, response?: array}
     */
    public function sendStructuredTemplate(
        string $phone,
        string $templateName,
        string $languageCode = 'en_US',
        array  $parameters = [],
        ?int   $orderId    = null
    ): array {
        $normalizedPhone = $this->normalizePhone($phone);
        $url = sprintf('%s/%s/messages', rtrim(config('whatsapp.api_url'), '/'), config('whatsapp.sender'));

        $components = [];
        if (!empty($parameters)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn($val) => [
                    'type' => 'text',
                    'text' => (string) $val
                ], $parameters)
            ];
        }

        try {
            $response = Http::withToken(config('whatsapp.api_token'))
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $normalizedPhone,
                    'type'              => 'template',
                    'template'          => [
                        'name'     => $templateName,
                        'language' => [
                            'code' => $languageCode
                        ],
                        'components' => $components
                    ]
                ]);

            $success = $response->successful();
            $status  = $success ? 'sent' : 'failed';

            $log = WhatsAppLog::create([
                'order_id' => $orderId,
                'phone'    => $phone,
                'message'  => "Template: {$templateName} (" . json_encode($parameters) . ")",
                'status'   => $status,
            ]);

            if ($success) {
                Log::info("WhatsApp structured template [{$templateName}] sent to {$phone}.", ['response' => $response->json()]);
                return ['success' => true, 'log' => $log, 'response' => $response->json()];
            }

            Log::error("WhatsApp structured template [{$templateName}] failed to {$phone}", [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            return [
                'success' => false,
                'error'   => $response->json('error.message') ?? "HTTP {$response->status()}",
                'log'     => $log
            ];

        } catch (\Throwable $e) {
            Log::error("WhatsApp API exception sending template [{$templateName}]: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
