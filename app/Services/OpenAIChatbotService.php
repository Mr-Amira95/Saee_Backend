<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ContactInformation;
use App\Models\Faq;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenAIChatbotService
{
    private const SYSTEM_PROMPT_TEMPLATE = <<<PROMPT
You are SAEE Logistics Assistant.

IMPORTANT: Always answer in the same language the customer used in their latest message. If they write in Arabic, reply in Arabic; if they write in English, reply in English.

Your responsibilities:
- Answer customer inquiries using only the provided FAQ data.
- Track orders using provided order information.
- Never invent shipping information or company policies.
- Be professional, concise, and friendly.
- Support Arabic and English — reply in the same language as the customer.
- If information is unavailable, politely ask the customer to contact SAEE support.

Formatting:
- Reply using simple HTML markup only, never Markdown.
- Wrap every paragraph in <p> tags. Use <strong> for emphasis, and <ul><li> for lists.
- Do not use <html>, <head>, <body>, <script>, <style>, or any event-handler attributes.
- Do not wrap the whole reply in a single <p> if it has multiple paragraphs or a list — use separate tags for each.

When tracking a shipment:
- If the context contains "NEEDS_IDENTIFIER", you MUST ask the customer to provide ONE of:
  (1) their order reference number, (2) the phone number used when placing the order,
  or (3) their full name as registered on the order. Do not make up tracking info.
- If multiple orders are returned, present each one clearly and concisely.
- If the order is not found, apologize and suggest the customer verify their details.
- Format every order's details as ONE <ul> with one <li> per field (order number,
  status, receiver name/phone, payment type/status, delivery amount). If tracking
  history is present, add a short <p> heading and a SEPARATE <ul> with one <li> per
  tracking-log entry. Never merge multiple fields or log entries into one line or
  one <li>.

Example of the exact shape to follow for a tracking reply (substitute real values
from the context, keep the same tag structure):
<p>Here are the details for your order:</p>
<ul>
  <li><strong>Order Number:</strong> 202407210001</li>
  <li><strong>Status:</strong> In Transit</li>
  <li><strong>Receiver:</strong> Ahmed Al-Rashid</li>
  <li><strong>Payment:</strong> Cash on Delivery — Pending</li>
</ul>
<p>Tracking history:</p>
<ul>
  <li>Created → Picked Up | 2024-07-21 09:00 | Package collected from sender</li>
  <li>Picked Up → In Transit | 2024-07-21 14:30 | Package left the sorting facility</li>
</ul>

Company contact information (use this exact information whenever asked for contact
details, phone number, email, address or working hours — never invent different values):
{CONTACT_INFO}
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

    // Common tracking/filler words that must never be treated as part of a
    // person's name, even when they appear as "2+ consecutive words" — this
    // is what previously caused a bare "تتبع الشحنة" ("track the shipment")
    // to be mistaken for the customer's name and searched against receivers.
    private const NAME_STOPWORDS = [
        'تتبع', 'الشحنة', 'شحنة', 'الطلب', 'طلب', 'طلبي', 'طلبى',
        'اين', 'أين', 'وين', 'متى', 'توصيل', 'الرجاء', 'ارجو', 'أرجو',
        'من', 'فضلك', 'لو', 'سمحت', 'سمحتم', 'حالة', 'وضعية', 'عن', 'على',
        'هل', 'يمكن', 'ممكن', 'اريد', 'أريد', 'اقدر', 'أقدر', 'كم', 'كيف',
    ];

    private const CLASSIFIER_SYSTEM_PROMPT = <<<PROMPT
You are an intent classifier for SAEE Logistics' customer-support chatbot.
Classify the customer's LATEST message into exactly one of two categories:

- tracking: the customer wants to know the status, location, or details of a
  shipment/order, or is supplying an order number, phone number, or full name
  in direct response to a request for identifying information.
- general_question: anything else — including questions about delivery
  coverage/countries served, payment methods, cash on delivery, changing a
  delivery address or time, business partnerships, contact details, or any
  other FAQ-style question. A message that merely mentions the words
  "delivery" or "deliver" is NOT automatically about tracking — for example
  "which countries do you deliver to?" and "can I change my delivery address?"
  are general_question, not tracking.

Reply with exactly one word and nothing else: tracking or general_question.
PROMPT;

    public function chat(string $sessionId, string $userMessage, ?int $userId = null, ?int $clientProfileId = null): array
    {
        $session = ChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => $userId],
        );

        $currentMessage = $session->messages()->create([
            'role'    => 'user',
            'message' => $userMessage,
        ]);

        $intent = $this->classifyIntent($userMessage, $session, $currentMessage->id);
        $lang   = $this->detectMessageLanguage($userMessage);

        $context = match ($intent) {
            'tracking'         => $this->buildTrackingContext($userMessage, $clientProfileId),
            'general_question' => $this->buildFaqContext($userMessage, $lang),
            default            => '',
        };

        $history = $session->messages()
            ->latest()
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $messages = $this->buildMessages($history, $userMessage, $context, $lang);

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

    /**
     * Primary intent-classification entry point.
     *
     * Precedence:
     *  1. Deterministic fast paths (no API call): a concrete order number/phone in
     *     the message, or a continuation of a previous "please provide your order
     *     number/phone/name" prompt.
     *  2. LLM-based classification for everything else (ambiguous natural language).
     *  3. Keyword-based detectIntent() as a resilient fallback if the LLM call
     *     fails, times out, or returns something unparseable.
     */
    public function classifyIntent(string $message, ChatSession $session, ?int $excludeMessageId = null): string
    {
        if ($this->extractOrderNumber($message) !== null || $this->extractPhone($message) !== null) {
            return 'tracking';
        }

        // Only treat this as a continuation of the tracking flow if the new
        // message actually looks like an identifier reply. Otherwise a
        // previous "please give me your name/phone/order number" prompt
        // would force EVERY subsequent message (including unrelated general
        // questions) back into tracking, trapping the conversation.
        if ($this->previousBotAskedForIdentifier($session) && $this->isPlausibleIdentifierReply($message)) {
            return 'tracking';
        }

        $recentHistory = $this->recentHistoryForClassification($session, $excludeMessageId);

        return $this->classifyIntentWithLLM($message, $recentHistory) ?? $this->detectIntent($message);
    }

    /**
     * True if $message plausibly answers a "what's your name/phone/order
     * number" prompt — i.e. it contains an extractable name, phone, or
     * order number. Deliberately strict: a plain reply like "thanks" or
     * "ok" must NOT be swept back into the tracking flow just because the
     * bot previously asked for an identifier.
     */
    private function isPlausibleIdentifierReply(string $message): bool
    {
        return $this->extractOrderNumber($message) !== null
            || $this->extractPhone($message) !== null
            || $this->extractName($message) !== null;
    }

    /**
     * Ask OpenAI to classify the message as 'tracking' or 'general_question'.
     * Returns null (never throws) on any failure so the caller falls back to the
     * keyword-based detectIntent().
     */
    private function classifyIntentWithLLM(string $message, Collection $recentHistory): ?string
    {
        $transcript = $recentHistory
            ->map(fn ($msg) => sprintf(
                '%s: %s',
                $msg->role === 'assistant' ? 'Assistant' : 'Customer',
                strip_tags($msg->message),
            ))
            ->implode("\n");

        $userContent = $transcript !== ''
            ? "Recent conversation:\n{$transcript}\n\nCustomer's latest message: \"{$message}\""
            : "Customer's latest message: \"{$message}\"";

        try {
            $response = Http::timeout(8)
                ->withToken(config('services.openai.key'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => config('services.openai.classifier_model'),
                    'temperature' => 0,
                    'max_tokens'  => 10,
                    'messages'    => [
                        ['role' => 'system', 'content' => self::CLASSIFIER_SYSTEM_PROMPT],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Intent classification API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            $raw = mb_strtolower(trim((string) $response->json('choices.0.message.content')));
            $raw = trim($raw, " \t\n\r\0\x0B.\"'");

            $result = match (true) {
                $raw === 'tracking' || $raw === 'general_question' => $raw,
                str_contains($raw, 'general_question')             => 'general_question',
                str_contains($raw, 'tracking')                     => 'tracking',
                default                                             => null,
            };

            if ($result === null) {
                Log::warning('Intent classification returned an unparseable response', ['raw' => $raw]);
            }

            return $result;
        } catch (Throwable $e) {
            Log::warning('Intent classification request failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function recentHistoryForClassification(ChatSession $session, ?int $excludeMessageId = null): Collection
    {
        return $session->messages()
            ->when($excludeMessageId !== null, fn ($q) => $q->where('id', '!=', $excludeMessageId))
            ->latest()
            ->limit(4)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Deterministic language detection based on which script (Arabic or Latin)
     * makes up more of the customer's message — independent of the site-wide
     * session locale toggle, so it always reflects what the customer is actually
     * typing right now. A majority vote (rather than "contains any Arabic
     * character") avoids a single embedded Arabic name/word in an otherwise
     * English message flipping the whole reply into Arabic.
     */
    public function detectMessageLanguage(string $message): string
    {
        $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}]/u', $message);
        $latinCount  = preg_match_all('/[A-Za-z]/', $message);

        return $arabicCount > $latinCount ? 'ar' : 'en';
    }

    /**
     * Keyword-based intent fallback, used by classifyIntent() only when the LLM
     * classifier is unavailable or fails. Kept public for backward-compat/tests.
     */
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

        // Arabic: two to four consecutive Arabic-letter words (real names
        // are short) — using the Arabic LETTERS range (0621–064A) rather
        // than the full Arabic block, which also includes punctuation like
        // "؟"; otherwise a question mark glued to the last word would be
        // swallowed into the "word" and let whole questions slip through.
        // Also reject outright if it's a question or contains a filler word
        // (e.g. reject "تتبع الشحنة" and "ما هي طرق الدفع المتاحة؟").
        if (! str_contains($message, '؟') && ! str_contains($message, '?')
            && preg_match('/[\x{0621}-\x{064A}]{2,}(?:\s+[\x{0621}-\x{064A}]{2,}){1,3}/u', $message, $m)) {
            $candidate = trim($m[0]);
            if (! $this->containsNameStopword($candidate)) {
                return $candidate;
            }
        }

        // English: two+ consecutive capitalised words that don't look like sentence starts
        if (preg_match('/\b([A-Z][a-z]{2,}(?:\s+(?:Al-?|El-?|Bin\s+)?[A-Z][a-z]{2,})+)\b/', $message, $m)) {
            $candidate = $m[1];
            if (! preg_match('/^(?:Please|Hello|Hi|Good|Thank|Sorry|Can|Could|Would|Where|What|When|How|My|The|I|We|Track|Order)\b/i', $candidate)
                && ! $this->containsNameStopword($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * True if any word of the candidate matches a tracking/filler stopword
     * or a tracking-intent keyword — used to stop generic phrases like
     * "تتبع الشحنة" or "اين طلبي" from being misread as a person's name.
     */
    private function containsNameStopword(string $candidate): bool
    {
        $words = preg_split('/\s+/u', mb_strtolower($candidate));

        foreach ($words as $word) {
            if (in_array($word, self::NAME_STOPWORDS, true)) {
                return true;
            }

            foreach (self::TRACKING_KEYWORDS as $keyword) {
                if ($word === mb_strtolower($keyword)) {
                    return true;
                }
            }
        }

        return false;
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
            ->with(['receiver', 'payment', 'trackingLogs' => fn ($q) => $q->latest()->limit(5)])
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
            $orders = $query->whereHas('receiver', function ($q) use ($digits, $short) {
                $q->where('receiver_phone', 'LIKE', "%{$digits}%")
                  ->orWhere('receiver_phone', 'LIKE', "%{$short}%");
            })
            ->with(['receiver', 'payment', 'trackingLogs' => fn ($q) => $q->latest()->limit(3)])
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
            $orders = $query->whereHas('receiver', function ($q) use ($name) {
                $q->where('receiver_name', 'LIKE', "%{$name}%");
            })
            ->with(['receiver', 'payment', 'trackingLogs' => fn ($q) => $q->latest()->limit(3)])
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

    public function buildFaqContext(string $message, string $lang = 'en'): string
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
                    $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(question, '$.en')) LIKE ?", ["%{$kw}%"])
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(question, '$.ar')) LIKE ?", ["%{$kw}%"])
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(answer, '$.en')) LIKE ?", ["%{$kw}%"])
                      ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(answer, '$.ar')) LIKE ?", ["%{$kw}%"]);
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
            $lines[] = "Q: {$faq->trans('question', $lang)}";
            $lines[] = "A: {$faq->trans('answer', $lang)}";
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
            "Receiver Name: {$order->receiver?->receiver_name}",
            "Receiver Phone: {$order->receiver?->receiver_phone}",
            "Payment Type: {$order->payment?->payment_type}",
            "Payment Status: {$order->payment_status}",
            "Delivery Amount: " . ($order->payment?->client_delivery_amount ?? $order->payment?->customer_delivery_amount ?? 'N/A'),
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

    private function buildSystemPrompt(string $lang = 'en'): string
    {
        $contact = ContactInformation::instance();

        $lines = array_filter([
            $contact->email ? "Email: {$contact->email}" : null,
            $contact->phone ? "Phone: {$contact->phone}" : null,
            $contact->address_text ? 'Address: ' . ($contact->trans('address_text', $lang)) : null,
            $contact->working_hours_text ? 'Working hours: ' . ($contact->trans('working_hours_text', $lang)) : null,
        ]);

        $contactInfo = $lines ? implode("\n", $lines) : 'Not configured yet — ask the customer to check the website footer.';

        $languageDirective = $lang === 'ar'
            ? "The customer's current message is in Arabic. You MUST reply entirely in Arabic (do not mix in English), regardless of what language was used earlier in this conversation."
            : "The customer's current message is in English. You MUST reply entirely in English, regardless of what language was used earlier in this conversation.";

        return str_replace('{CONTACT_INFO}', $contactInfo, self::SYSTEM_PROMPT_TEMPLATE) . "\n\n" . $languageDirective;
    }

    private function buildMessages(
        Collection $history,
        string $userMessage,
        string $context,
        string $lang = 'en',
    ): array {
        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt($lang)],
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
        return "<p>I'm sorry, I'm unable to process your request at the moment. Please contact SAEE customer support for assistance.</p>";
    }
}
