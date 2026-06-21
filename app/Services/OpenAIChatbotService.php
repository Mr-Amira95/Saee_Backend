<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Faq;
use App\Models\Order;
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
PROMPT;

    private const TRACKING_KEYWORDS = [
        'track', 'order', 'shipment', 'package', 'delivery', 'deliver',
        'where is', 'status', 'تتبع', 'طلب', 'شحنة', 'اين', 'أين',
        'وين', 'متى', 'توصيل',
    ];

    public function chat(string $sessionId, string $userMessage, ?int $userId = null): array
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

        $context = match ($intent) {
            'tracking'         => $this->buildTrackingContext($userMessage),
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
        // Tight: 10-15 digit numeric (matches existing CCYYMMDDSSSS format)
        if (preg_match('/\b(\d{10,15})\b/', $message, $m)) {
            return $m[1];
        }

        // Broad: 5-20 char alphanumeric with optional hyphens (future-proof)
        if (preg_match('/\b([A-Za-z0-9]{5,20}(?:-[A-Za-z0-9]+)*)\b/', $message, $m)) {
            return $m[1];
        }

        return null;
    }

    public function buildTrackingContext(string $message): string
    {
        $number = $this->extractOrderNumber($message);

        if ($number === null) {
            return 'No order number found in the customer message.';
        }

        $order = Order::where('order_number', $number)
            ->with(['trackingLogs' => fn ($q) => $q->latest()->limit(5)])
            ->first();

        if ($order === null) {
            return "Order {$number} was not found in the system.";
        }

        $lines = [
            "Order Number: {$order->order_number}",
            "Current Status: {$order->status}",
            "Receiver: {$order->receiver_name}",
            "Payment Type: {$order->payment_type}",
            "Payment Status: {$order->payment_status}",
            "Delivery Amount: {$order->delivery_amount}",
        ];

        if ($order->notes) {
            $lines[] = "Notes: {$order->notes}";
        }

        if ($order->trackingLogs->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Tracking History:';

            foreach ($order->trackingLogs as $log) {
                $from = $log->from_status ?? 'N/A';
                $lines[] = "{$from} → {$log->to_status} | {$log->created_at->toDateTimeString()} | {$log->description}";
            }
        }

        return implode("\n", $lines);
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

    private function buildMessages(
        \Illuminate\Support\Collection $history,
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

        // Replace the last user message entry with the context-enriched version
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
