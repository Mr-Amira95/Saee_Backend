<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\Faq;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAIChatbotService
{
    private const SYSTEM_PROMPT = <<<PROMPT
You are SAEE Logistics Assistant.

Your responsibilities:
- Answer customer inquiries using only the provided FAQ data.
- Track orders using provided order information.
- Never invent shipping information or company policies.
- Be professional, concise, and friendly.
- Support Arabic and English — reply in the same language as the customer.
- If information is unavailable, politely ask the customer to contact SAEE support.

When tracking a shipment:
- If the context contains "NEEDS_IDENTIFIER", you MUST ask the customer to provide ONE of:
  (1) their order reference number, (2) the phone number used when placing the order,
  or (3) their full name as registered on the order. Do not make up tracking info.
- If multiple orders are returned, present each one clearly and concisely.
- If the order is not found, apologize and suggest the customer verify their details.
PROMPT;

    private const TRACKING_KEYWORDS = [
        'track', 'order', 'shipment', 'package', 'delivery', 'deliver',
        'where is', 'status', 'تتبع', 'طلب', 'شحنة', 'اين', 'أين',
        'وين', 'متى', 'توصيل',
    ];

    // Phrases that indicate the bot previously asked for an identifier
    private const IDENTIFIER_REQUEST_PHRASES = [
        'order reference', 'order number', 'reference number', 'tracking number',
        'phone number', 'full name', 'your name', 'registered name',
        'رقم الطلب', 'رقم الهاتف', 'اسمك', 'اسم المستلم', 'الاسم الكامل',
    ];

    public function chat(string $sessionId, string $userMessage, ?int $userId = null, ?int $clientProfileId = null): array
    {
        $session = ChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => $userId],
        );

        $session->messages()->create([
            'role'    => 'user',
            'message' => $userMessage,
        ]);

        $intent = $this->detectIntent($userMessage);

        // If the previous bot turn was asking for identifier info, force tracking intent
        // even when the reply contains no tracking keywords (e.g. user just says "Ahmed Al-Rashid")
        if ($intent === 'general_question' && $this->previousBotAskedForIdentifier($session)) {
            $intent = 'tracking';
        }

        $context = match ($intent) {
            'tracking'         => $this->buildTrackingContext($userMessage, $clientProfileId),
            'general_question' => $this->buildFaqContext($userMessage),
            default            => '',
        };

        $history = $session->messages()
            ->latest()
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $messages = $this->buildMessages($history, $userMessage, $context);

        $result = $this->sendToOpenAI($messages);

        $session->messages()->create([
            'role'    => 'assistant',
            'message' => $result['reply'],
            'tokens'  => $result['tokens'],
        ]);

        return [
            'reply'  => $result['reply'],
            'intent' => $intent,
        ];
    }

    public function detectIntent(string $message): string
    {
        $lower = mb_strtolower($message);

        foreach (self::TRACKING_KEYWORDS as $keyword) {
            if (str_contains($lower, $keyword)) {
                return 'tracking';
            }
        }

        if ($this->extractOrderNumber($message) !== null) {
            return 'tracking';
        }

        return 'general_question';
    }

    public function extractOrderNumber(string $message): ?string
    {
        // Tight: 10–15 digit numeric — matches the system's CCYYMMDDSSSS format
        if (preg_match('/\b(\d{10,15})\b/', $message, $m)) {
            return $m[1];
        }

        // Broad: alphanumeric that contains at least one digit (prevents matching plain words/names)
        if (preg_match('/\b(?=[A-Za-z0-9\-]*\d)[A-Za-z0-9]{5,20}(?:-[A-Za-z0-9]+)*\b/', $message, $m)) {
            return $m[0];
        }

        return null;
    }

    public function extractPhone(string $message): ?string
    {
        // Saudi: +9665XXXXXXXX, 05XXXXXXXX
        // Jordanian: +9627XXXXXXXX, 07XXXXXXXX
        // Gulf/MENA variations with optional spaces/dashes
        $patterns = [
            '/\+9\d[\d\s\-]{7,13}/',   // +9xx international (e.g. +966 5X...)
            '/\b00\d{9,13}\b/',         // 009xx
            '/\b0[5-9]\d{7,9}\b/',      // 05x–09x local (Gulf + Jordan)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $m)) {
                return preg_replace('/[\s\-]/', '', $m[0]);
            }
        }

        return null;
    }

    public function extractName(string $message): ?string
    {
        // Explicit: "my name is X", "اسمي X", "name: X", etc.
        if (preg_match(
            '/(?:my name is|name[:\s]+|i\'?m\s+|i am\s+|اسمي\s+|اسم[:\s]+)\s*([A-Za-z\x{0600}-\x{06FF}][A-Za-z\x{0600}-\x{06FF}\s]{2,50})/iu',
            $message,
            $m,
        )) {
            return trim(preg_replace('/\s+/', ' ', $m[1]));
        }

        // Arabic: two or more consecutive Arabic words
        if (preg_match('/[\x{0600}-\x{06FF}]{2,}(?:\s+[\x{0600}-\x{06FF}]{2,})+/u', $message, $m)) {
            return trim($m[0]);
        }

        // English: two+ consecutive capitalised words that don't look like sentence starts
        if (preg_match('/\b([A-Z][a-z]{2,}(?:\s+(?:Al-?|El-?|Bin\s+)?[A-Z][a-z]{2,})+)\b/', $message, $m)) {
            $candidate = $m[1];
            if (! preg_match('/^(?:Please|Hello|Hi|Good|Thank|Sorry|Can|Could|Would|Where|What|When|How|My|The|I|We|Track|Order)\b/i', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function buildTrackingContext(string $message, ?int $clientProfileId = null): string
    {
        $phone  = $this->extractPhone($message);
        $refNum = $this->extractOrderNumber($message);
        $name   = $this->extractName($message);

        // If phone digits are a subset of refNum it's the same token — don't double-search
        if ($phone && $refNum) {
            $phoneDigits = preg_replace('/\D/', '', $phone);
            $refDigits   = preg_replace('/\D/', '', $refNum);
            if (str_contains($refDigits, $phoneDigits) || str_contains($phoneDigits, $refDigits)) {
                $refNum = null;
            }
        }

        // ── 1. Search by reference / order number ────────────────────────
        if ($refNum) {
            $query = Order::query();
            if ($clientProfileId !== null) {
                $query->where('client_profile_id', $clientProfileId);
            }
            $order = $query->where(function ($q) use ($refNum) {
                $q->where('order_number', $refNum)
                  ->orWhere('batch_number', $refNum);
            })
            ->with(['trackingLogs' => fn ($q) => $q->latest()->limit(5)])
            ->first();

            if ($order) {
                return $this->formatOrderContext($order);
            }

            // Not found — still fall through to phone/name if present
            if (! $phone && ! $name) {
                return "Order reference \"{$refNum}\" was not found in the system. Ask the customer to verify the number or try their phone number or full name instead.";
            }
        }

        // ── 2. Search by phone ────────────────────────────────────────────
        if ($phone) {
            $digits = preg_replace('/\D/', '', $phone);
            $short  = ltrim($digits, '0');

            $query = Order::query();
            if ($clientProfileId !== null) {
                $query->where('client_profile_id', $clientProfileId);
            }
            $orders = $query->where(function ($q) use ($digits, $short) {
                $q->where('receiver_phone', 'LIKE', "%{$digits}%")
                  ->orWhere('receiver_phone', 'LIKE', "%{$short}%");
            })
            ->with(['trackingLogs' => fn ($q) => $q->latest()->limit(3)])
            ->latest()
            ->limit(5)
            ->get();

            if ($orders->isNotEmpty()) {
                return $this->formatMultipleOrdersContext($orders, "phone number {$phone}");
            }

            if (! $name) {
                return "No orders found for phone number {$phone}. Ask the customer to verify the number or provide their order reference or full name.";
            }
        }

        // ── 3. Search by name ─────────────────────────────────────────────
        if ($name) {
            $query = Order::query();
            if ($clientProfileId !== null) {
                $query->where('client_profile_id', $clientProfileId);
            }
            $orders = $query->where('receiver_name', 'LIKE', "%{$name}%")
                ->with(['trackingLogs' => fn ($q) => $q->latest()->limit(3)])
                ->latest()
                ->limit(5)
                ->get();

            if ($orders->isNotEmpty()) {
                return $this->formatMultipleOrdersContext($orders, "name \"{$name}\"");
            }

            return "No orders found for name \"{$name}\". Ask the customer to verify their name or provide their order reference or phone number.";
        }

        // ── 4. No identifier found at all ─────────────────────────────────
        return 'NEEDS_IDENTIFIER: The customer wants to track a shipment but has not provided any identifying information. You MUST ask them to provide ONE of: (1) their order reference or tracking number, (2) the phone number used when placing the order, or (3) their full name as registered on the order.';
    }

    public function buildFaqContext(string $message): string
    {
        $keywords = array_values(array_filter(
            preg_split('/\s+/', mb_strtolower($message)),
            fn ($w) => mb_strlen($w) > 3,
        ));

        if (empty($keywords)) {
            return '';
        }

        $faqs = Faq::where('status', 'active')
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('question', 'LIKE', "%{$kw}%")
                      ->orWhere('answer', 'LIKE', "%{$kw}%");
                }
            })
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        if ($faqs->isEmpty()) {
            return '';
        }

        $lines = ['Relevant FAQs:'];

        foreach ($faqs as $faq) {
            $lines[] = '';
            $lines[] = "Q: {$faq->question}";
            $lines[] = "A: {$faq->answer}";
            $lines[] = "Category: {$faq->category}";
        }

        return implode("\n", $lines);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function previousBotAskedForIdentifier(ChatSession $session): bool
    {
        $lastBotMessage = $session->messages()
            ->where('role', 'assistant')
            ->latest()
            ->value('message');

        if (! $lastBotMessage) {
            return false;
        }

        $lower = mb_strtolower($lastBotMessage);

        foreach (self::IDENTIFIER_REQUEST_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function formatOrderContext(Order $order): string
    {
        $lines = [
            "Order Number: {$order->order_number}",
            "Current Status: {$order->status}",
            "Receiver Name: {$order->receiver_name}",
            "Receiver Phone: {$order->receiver_phone}",
            "Payment Type: {$order->payment_type}",
            "Payment Status: {$order->payment_status}",
            "Delivery Amount: {$order->delivery_amount}",
        ];

        if ($order->notes) {
            $lines[] = "Notes: {$order->notes}";
        }

        if ($order->relationLoaded('trackingLogs') && $order->trackingLogs->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Tracking History:';

            foreach ($order->trackingLogs as $log) {
                $from = $log->from_status ?? 'N/A';
                $lines[] = "{$from} → {$log->to_status} | {$log->created_at->toDateTimeString()} | {$log->description}";
            }
        }

        return implode("\n", $lines);
    }

    private function formatMultipleOrdersContext(Collection $orders, string $searchedBy = ''): string
    {
        $count = $orders->count();
        $lines = ["Found {$count} order(s)" . ($searchedBy ? " matching {$searchedBy}" : '') . ':'];

        foreach ($orders as $i => $order) {
            $lines[] = '';
            $lines[] = '--- Order ' . ($i + 1) . ' ---';
            $lines[] = $this->formatOrderContext($order);
        }

        return implode("\n", $lines);
    }

    private function buildMessages(
        Collection $history,
        string $userMessage,
        string $context,
    ): array {
        $messages = [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
        ];

        foreach ($history as $msg) {
            if ($msg->role === 'system') {
                continue;
            }
            $messages[] = [
                'role'    => $msg->role,
                'content' => $msg->message,
            ];
        }

        $lastUserContent = $userMessage;
        if ($context !== '') {
            $lastUserContent .= "\n\n--- Context ---\n" . $context;
        }

        // Replace the last user message with the context-enriched version
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if ($messages[$i]['role'] === 'user') {
                $messages[$i]['content'] = $lastUserContent;
                break;
            }
        }

        return $messages;
    }

    private function sendToOpenAI(array $messages): array
    {
        try {
            $response = Http::timeout(30)
                ->withToken(config('services.openai.key'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => config('services.openai.model'),
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return ['reply' => $this->fallbackReply(), 'tokens' => null];
            }

            $reply  = $response->json('choices.0.message.content') ?? $this->fallbackReply();
            $tokens = $response->json('usage.total_tokens');

            return ['reply' => $reply, 'tokens' => $tokens];
        } catch (Throwable $e) {
            Log::error('OpenAI request failed', ['error' => $e->getMessage()]);

            return ['reply' => $this->fallbackReply(), 'tokens' => null];
        }
    }

    private function fallbackReply(): string
    {
        return "I'm sorry, I'm unable to process your request at the moment. Please contact SAEE customer support for assistance.";
    }
}
