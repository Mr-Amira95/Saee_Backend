<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WhatsAppLog;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookService
{
    /**
     * Entry point — parse the payload and route each message to the right handler.
     */
    public function handleIncomingMessage(array $payload): void
    {
        Log::info('WhatsApp webhook: processing payload', ['payload' => $payload]);

        foreach ($this->extractMessages($payload) as $message) {
            $this->handleMessage($message);
        }

        foreach ($this->extractStatuses($payload) as $status) {
            $this->handleStatus($status);
        }
    }

    /**
     * Apply a delivery status update (sent → delivered → read, or failed) to the
     * outbound log it belongs to, matched by Meta's message id.
     */
    public function handleStatus(array $status): void
    {
        $rank = ['sent' => 1, 'delivered' => 2, 'read' => 3];
        $new  = $status['status'] ?? null;
        $log  = empty($status['id']) ? null : WhatsAppLog::where('meta->wamid', $status['id'])->first();

        if (! $log || ! $new) {
            return;
        }

        if ($new === 'failed') {
            $error = $status['errors'][0] ?? [];
            $log->update([
                'status' => 'failed',
                'meta'   => [...($log->meta ?? []), 'error' => $error['error_data']['details'] ?? $error['message'] ?? $error['title'] ?? null],
            ]);

            return;
        }

        // Statuses can arrive out of order — never step backwards.
        if (isset($rank[$new]) && $rank[$new] > ($rank[$log->status] ?? 0)) {
            $log->update(['status' => $new]);
        }
    }

    /**
     * Persist every incoming message to whatsapp_logs regardless of type, then
     * run any type-specific side effects (e.g. saving a shared location).
     */
    public function handleMessage(array $message): void
    {
        $phone = $message['from'] ?? null;
        $type  = $message['type'] ?? 'unknown';

        if (! $phone) {
            Log::warning('WhatsApp webhook: message missing sender phone.', ['message' => $message]);
            return;
        }

        // Meta may deliver the same webhook more than once, and the job itself retries.
        if (! empty($message['id']) && WhatsAppLog::where('meta->wamid', $message['id'])->exists()) {
            return;
        }

        $order = $this->findOrderByPhone($phone);

        if (! $order) {
            Log::warning('WhatsApp webhook: no active order found for phone.', ['phone' => $phone]);
        } else {
            Log::info('WhatsApp webhook: order matched by phone.', [
                'phone'        => $phone,
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);
        }

        [$body, $meta] = $this->describeMessage($type, $message);

        $meta = array_filter([
            ...($meta ?? []),
            'profile_name' => $message['_profile_name'] ?? null,
            'wamid'        => $message['id'] ?? null,
        ], fn ($value) => $value !== null) ?: null;

        WhatsAppLog::create([
            'order_id'     => $order?->id,
            'phone'        => $phone,
            'message'      => $body,
            'status'       => 'received',
            'direction'    => 'inbound',
            'message_type' => $type,
            'meta'         => $meta,
        ]);

        if ($type === 'location' && $order && ! empty($message['location'])) {
            $this->saveCustomerLocation($order, $message['location']);
        }
    }

    /**
     * Build a human-readable message body and a raw meta payload for a given
     * incoming message type.
     *
     * @return array{0: string, 1: ?array}
     */
    private function describeMessage(string $type, array $message): array
    {
        return match ($type) {
            'text' => [$message['text']['body'] ?? '', null],
            'location' => [
                $message['location']['address']
                    ?? $message['location']['name']
                    ?? trim(($message['location']['latitude'] ?? '') . ', ' . ($message['location']['longitude'] ?? '')),
                $message['location'] ?? null,
            ],
            'image', 'video', 'audio', 'document', 'sticker' => [
                $message[$type]['caption'] ?? '',
                ['media' => $this->fetchMedia($message[$type] ?? [])],
            ],
            default => ["[{$type}]", $message[$type] ?? $message],
        };
    }

    /**
     * Download an incoming media file right away (Meta's links expire within
     * minutes). Never throws: on failure the media id is kept so the admin
     * inbox can retry when the file is opened.
     */
    private function fetchMedia(array $media): array
    {
        $info = array_filter([
            'id'       => $media['id'] ?? null,
            'mime'     => isset($media['mime_type']) ? strtolower(trim(explode(';', $media['mime_type'])[0])) : null,
            'filename' => $media['filename'] ?? null,
            'voice'    => ($media['voice'] ?? false) ?: null,
        ], fn ($value) => $value !== null);

        if (empty($info['id'])) {
            return $info;
        }

        try {
            return [...$info, ...app(WhatsAppMediaService::class)->download($info['id'])];
        } catch (\Throwable $e) {
            Log::warning('WhatsApp webhook: media download failed.', ['media_id' => $info['id'], 'error' => $e->getMessage()]);

            return [...$info, 'download_error' => $e->getMessage()];
        }
    }

    /**
     * Find the most recent active order whose receiver_phone matches any of the
     * normalised candidate forms of the incoming phone number.
     */
    public function findOrderByPhone(string $phone): ?Order
    {
        $candidates = $this->generatePhoneCandidates($phone);

        $order = Order::whereHas('receiver', fn ($q) => $q->whereIn('receiver_phone', $candidates))
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
            ->latest()
            ->first();

        if (! $order) {
            Log::debug('WhatsApp webhook: phone candidates tried.', ['candidates' => $candidates]);
        }

        return $order;
    }

    /**
     * Persist the customer's shared location on the matched order.
     */
    public function saveCustomerLocation(Order $order, array $locationData): void
    {
        $latitude  = $locationData['latitude']  ?? null;
        $longitude = $locationData['longitude'] ?? null;
        $name      = $locationData['name']      ?? null;
        $address   = $locationData['address']   ?? null;

        // Prefer address string; fall back to place name
        $addressText = $address ?? $name ?? null;

        $order->receiver()->update([
            'receiver_latitude'    => $latitude,
            'receiver_longitude'   => $longitude,
            'location_received_at' => now(),
            // Only overwrite the address field when the incoming data has content
            'address_text'         => $addressText ?? $order->receiver->address_text,
        ]);

        Log::info('WhatsApp webhook: customer location saved.', [
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'latitude'     => $latitude,
            'longitude'    => $longitude,
            'address'      => $addressText,
        ]);
    }

    /**
     * Pull the messages array out of either a Meta nested payload or a flat test payload.
     * Each Meta message gets the sender's WhatsApp profile name (from the sibling
     * contacts[] list) attached as "_profile_name".
     *
     * Meta format:
     *   entry[].changes[].value.messages[]
     *   entry[].changes[].value.contacts[].{wa_id, profile.name}
     */
    private function extractMessages(array $payload): array
    {
        if (isset($payload['entry'])) {
            $messages = [];
            foreach ($payload['entry'] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $profileNames = collect($change['value']['contacts'] ?? [])
                        ->mapWithKeys(fn ($contact) => [($contact['wa_id'] ?? '') => $contact['profile']['name'] ?? null]);

                    foreach ($change['value']['messages'] ?? [] as $msg) {
                        $msg['_profile_name'] = $profileNames[$msg['from'] ?? ''] ?? null;
                        $messages[] = $msg;
                    }
                }
            }
            return $messages;
        }

        // Flat format used in direct tests
        if (isset($payload['type'])) {
            return [$payload];
        }

        return [];
    }

    /**
     * Pull delivery status updates out of a Meta payload:
     *   entry[].changes[].value.statuses[]
     */
    private function extractStatuses(array $payload): array
    {
        $statuses = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                foreach ($change['value']['statuses'] ?? [] as $status) {
                    $statuses[] = $status;
                }
            }
        }

        return $statuses;
    }

    /**
     * Build every plausible phone string from an incoming number so we can match
     * against however the number is stored in receiver_phone.
     *
     * Example: incoming "+9627XXXXXXXX" generates:
     *   "+9627XXXXXXXX", "9627XXXXXXXX", "09627XXXXXXXX", "7XXXXXXXX", "07XXXXXXXX"
     */
    private function generatePhoneCandidates(string $phone): array
    {
        $digits = preg_replace('/\D/', '', $phone);

        $candidates = [
            $phone,             // as-is  (+9627XXXXXXXX)
            '+' . $digits,      // always with + prefix
            $digits,            // digits only
            '0' . $digits,      // leading zero + all digits
        ];

        // Strip Jordan country code 962 → local 07XXXXXXXX / 7XXXXXXXX
        if (str_starts_with($digits, '962') && strlen($digits) >= 10) {
            $local        = substr($digits, 3);
            $candidates[] = $local;
            $candidates[] = '0' . $local;
        }

        return array_unique(array_filter($candidates));
    }
}
