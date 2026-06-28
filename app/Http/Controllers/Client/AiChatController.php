<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = session('client_chat_session_id');
        if (!$sessionId) {
            $sessionId = 'c_' . Str::uuid()->toString();
            session(['client_chat_session_id' => $sessionId]);
        }

        return view('client.ai-chat.index', compact('sessionId'));
    }

    public function reset(Request $request)
    {
        // Generate a brand new UUID session ID
        $newSessionId = 'c_' . Str::uuid()->toString();
        session(['client_chat_session_id' => $newSessionId]);

        return redirect()->route('client.ai-chat.index')
            ->with('success', 'A new AI chat session has been started.');
    }
}
