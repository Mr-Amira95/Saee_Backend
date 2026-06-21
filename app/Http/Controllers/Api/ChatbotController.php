<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Chatbot\SendMessageRequest;
use App\Models\ChatSession;
use App\Services\OpenAIChatbotService;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    public function __construct(private OpenAIChatbotService $chatbotService) {}

    public function message(SendMessageRequest $request): JsonResponse
    {
        $result = $this->chatbotService->chat(
            sessionId:   $request->input('session_id'),
            userMessage: $request->input('message'),
        );

        return response()->json([
            'success' => true,
            'reply'   => $result['reply'],
            'intent'  => $result['intent'],
        ]);
    }

    public function history(string $sessionId): JsonResponse
    {
        $session = ChatSession::where('session_id', $sessionId)->first();

        if ($session === null) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $messages = $session->messages()
            ->oldest()
            ->get()
            ->map(fn ($msg) => [
                'role'       => $msg->role,
                'message'    => $msg->message,
                'created_at' => $msg->created_at->toDateTimeString(),
            ]);

        return response()->json(['success' => true, 'data' => $messages]);
    }
}
