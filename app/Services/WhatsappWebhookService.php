<?php

namespace App\Services;

use App\Models\Order;
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
            $type = $message['type'] ?? null;

            if ($type === 'location') {
                $this->handleLocationMessage($message);
            } else {
                Log::info('WhatsApp webhook: unsupported message type, skipping.', ['type' => $type]);
            }
        }
    }

    /**
     * Process a location message: identify the order and save coordinates.
     */
    public function handleLocationMessage(array $message): void
    {
        $phone        = $message['from']     ?? null;
        $locationData = $message['location'] ?? null;

        if (! $phone || ! $locationData) {
            Log::warning('WhatsApp webhook: location message missing phone or location data.', [
                'message' => $message,
            ]);
            return;
        }

        $order = $this->findOrderByPhone($phone);

        if (! $order) {
            Log::warning('WhatsApp webhook: no active order found for phone.', ['phone' => $phone]);
            return;
        }

        Log::info('WhatsApp webhook: order matched by phone.', [
            'phone'        => $phone,
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
        ]);

        $this->saveCustomerLocation($order, $locationData);
    }

    /**
     * Find the most recent active order whose receiver_phone matches any of the
     * normalised candidate forms of the incoming phone number.
     */
    public function findOrderByPhone(string $phone): ?Order
    {
        $candidates = $this->generatePhoneCandidates($phone);

        $order = Order::whereIn('receiver_phone', $candidates)
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

        $order->update([
            'receiver_latitude'    => $latitude,
            'receiver_longitude'   => $longitude,
            'location_received_at' => now(),
            // Only overwrite address fields when the incoming data has content
            'address_text'         => $addressText ?? $order->address_text,
            'address_location'     => ($latitude && $longitude)
                ? "{$latitude},{$longitude}"
                : $order->address_location,
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
     *
     * Meta format:
     *   entry[].changes[].value.messages[]
     */
    private function extractMessages(array $payload): array
    {
        if (isset($payload['entry'])) {
            $messages = [];
            foreach ($payload['entry'] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    foreach ($change['value']['messages'] ?? [] as $msg) {
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
