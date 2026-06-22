<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class AiConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatSession::with('user')
            ->withCount('messages')
            ->withMax('messages', 'created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('session_id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                     ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            if ($request->input('type') === 'anonymous') {
                $query->whereNull('user_id');
            } elseif ($request->input('type') === 'authenticated') {
                $query->whereNotNull('user_id');
            }
        }

        $sessions = $query->latest('updated_at')->paginate(25)->withQueryString();

        $stats = [
            'total'   => ChatSession::count(),
            'today'   => ChatSession::whereDate('created_at', today())->count(),
            'anon'    => ChatSession::whereNull('user_id')->count(),
            'messages'=> ChatMessage::count(),
        ];

        return view('admin.ai-conversations.index', compact('sessions', 'stats'));
    }

    public function show(ChatSession $aiConversation)
    {
        $aiConversation->load(['user', 'messages' => fn ($q) => $q->orderBy('created_at')]);

        return view('admin.ai-conversations.show', ['session' => $aiConversation]);
    }

    public function destroy(ChatSession $aiConversation)
    {
        $aiConversation->delete();

        return redirect()->route('admin.ai-conversations.index')
            ->with('success', 'Conversation session deleted.');
    }
}
