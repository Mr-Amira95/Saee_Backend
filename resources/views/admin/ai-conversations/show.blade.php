@extends('admin.layouts.app')

@section('title', 'Conversation — ' . Str::limit($session->session_id, 16))
@section('page-title', 'AI Conversation')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.ai-conversations.index') }}">AI Conversations</a>
    <span class="sep">/</span>
    <span class="current">{{ Str::limit($session->session_id, 20, '…') }}</span>
@endsection

@section('head')
<style>
    .convo-layout { display: grid; grid-template-columns: 280px 1fr; gap: 18px; align-items: start; }
    @media(max-width:900px) { .convo-layout { grid-template-columns: 1fr; } }

    .convo-meta {
        background: var(--card); border: 1px solid var(--bdr);
        border-radius: 14px; padding: 20px; backdrop-filter: blur(8px);
        position: sticky; top: 0;
    }
    .convo-meta-title {
        font-size: .68rem; font-weight: 700; color: var(--text-dim);
        letter-spacing: .1em; text-transform: uppercase;
        margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--bdr);
    }
    .meta-row { display: flex; flex-direction: column; gap: 3px; margin-bottom: 14px; }
    .meta-lbl { font-size: .68rem; color: var(--text-dim); font-weight: 600; text-transform: uppercase; letter-spacing: .07em; }
    .meta-val { font-size: .83rem; color: var(--text); word-break: break-all; }

    .chat-card {
        background: var(--card); border: 1px solid var(--bdr);
        border-radius: 14px; backdrop-filter: blur(8px); overflow: hidden;
    }
    .chat-header {
        padding: 14px 20px; border-bottom: 1px solid var(--bdr);
        display: flex; align-items: center; gap: 10px;
    }
    .chat-header-icon {
        width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
        background: linear-gradient(135deg,#4f46e5,#818cf8);
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 700; color: white;
    }
    .chat-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }

    .msg-row { display: flex; gap: 10px; }
    .msg-row.user { flex-direction: row-reverse; }
    .msg-row.system { justify-content: center; }

    .msg-avatar {
        width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; font-weight: 700; color: white;
        align-self: flex-end;
    }
    .msg-avatar.avatar-user      { background: linear-gradient(135deg,#dc2626,#7f1d1d); }
    .msg-avatar.avatar-assistant { background: linear-gradient(135deg,#4f46e5,#818cf8); }

    .msg-bubble {
        max-width: 72%; padding: 10px 14px; border-radius: 14px;
        font-size: .855rem; line-height: 1.6; word-break: break-word;
    }
    .msg-bubble.bubble-user {
        background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.18);
        border-bottom-right-radius: 4px; color: var(--text);
    }
    .msg-bubble.bubble-assistant {
        background: rgba(79,70,229,.1); border: 1px solid rgba(79,70,229,.18);
        border-bottom-left-radius: 4px; color: var(--text);
    }
    .msg-bubble.bubble-system {
        background: rgba(255,255,255,.04); border: 1px solid var(--bdr);
        border-radius: 8px; color: var(--text-dim); font-size: .78rem;
        max-width: 90%; text-align: center; padding: 7px 14px;
    }
    .msg-meta {
        font-size: .68rem; color: var(--text-dim);
        margin-top: 4px; display: flex; align-items: center; gap: 6px;
    }
    .msg-row.user .msg-meta { justify-content: flex-end; }
    .msg-tokens {
        background: rgba(255,255,255,.05); border-radius: 4px;
        padding: 1px 5px; font-size: .63rem; color: var(--text-dim);
    }

    .chat-empty { padding: 50px 20px; text-align: center; color: var(--text-dim); font-size: .85rem; }
</style>
@endsection

@section('content')

<div style="margin-bottom:16px">
    <a href="{{ route('admin.ai-conversations.index') }}" class="btn-secondary" style="padding:8px 14px;font-size:.82rem">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back
    </a>
</div>

<div class="convo-layout">

    {{-- Meta sidebar --}}
    <div class="convo-meta">
        <div class="convo-meta-title">Session Info</div>

        <div class="meta-row">
            <span class="meta-lbl">Session ID</span>
            <span class="meta-val" style="font-family:monospace;font-size:.78rem">{{ $session->session_id }}</span>
        </div>

        <div class="meta-row">
            <span class="meta-lbl">User</span>
            @if($session->user)
                <span class="meta-val" style="font-weight:600">{{ $session->user->name }}</span>
                <span style="font-size:.74rem;color:var(--text-sub)">{{ $session->user->email }}</span>
                <span class="badge badge-pv" style="margin-top:4px;width:fit-content">{{ $session->user->role }}</span>
            @else
                <span class="badge badge-no" style="width:fit-content">Anonymous Guest</span>
            @endif
        </div>

        <div class="meta-row">
            <span class="meta-lbl">Messages</span>
            <span class="meta-val" style="font-size:1.2rem;font-weight:800">{{ $session->messages->count() }}</span>
        </div>

        <div class="meta-row">
            <span class="meta-lbl">Started</span>
            <span class="meta-val">{{ $session->created_at->format('d M Y') }}</span>
            <span style="font-size:.74rem;color:var(--text-sub)">{{ $session->created_at->format('H:i:s') }}</span>
        </div>

        <div class="meta-row">
            <span class="meta-lbl">Last Activity</span>
            <span class="meta-val">{{ $session->updated_at->diffForHumans() }}</span>
        </div>

        @php
            $totalTokens = $session->messages->sum('tokens');
            $userMsgs    = $session->messages->where('role', 'user')->count();
            $asstMsgs    = $session->messages->where('role', 'assistant')->count();
        @endphp

        @if($totalTokens > 0)
        <div class="meta-row">
            <span class="meta-lbl">Total Tokens</span>
            <span class="meta-val" style="font-weight:700">{{ number_format($totalTokens) }}</span>
        </div>
        @endif

        <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap">
            <span style="font-size:.72rem;color:var(--text-sub)">
                <span style="color:#fca5a5;font-weight:700">{{ $userMsgs }}</span> user
            </span>
            <span style="font-size:.72rem;color:var(--text-sub)">
                <span style="color:#818cf8;font-weight:700">{{ $asstMsgs }}</span> assistant
            </span>
        </div>
    </div>

    {{-- Chat thread --}}
    <div class="chat-card">
        <div class="chat-header">
            <div class="chat-header-icon">AI</div>
            <div>
                <div style="font-size:.88rem;font-weight:700">Conversation Thread</div>
                <div style="font-size:.74rem;color:var(--text-sub)">{{ $session->messages->count() }} messages</div>
            </div>
        </div>

        @if($session->messages->isEmpty())
            <div class="chat-empty">No messages in this session.</div>
        @else
        <div class="chat-body">
            @foreach($session->messages as $message)

                @if($message->role === 'system')
                    <div class="msg-row system">
                        <div>
                            <div class="msg-bubble bubble-system">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:-1px"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                System prompt
                            </div>
                        </div>
                    </div>

                @elseif($message->role === 'user')
                    <div class="msg-row user">
                        <div class="msg-avatar avatar-user">
                            {{ $session->user ? strtoupper(substr($session->user->name, 0, 2)) : 'U' }}
                        </div>
                        <div>
                            <div class="msg-bubble bubble-user">{{ $message->message }}</div>
                            <div class="msg-meta">
                                {{ $message->created_at->format('H:i') }}
                                @if($message->tokens)
                                    <span class="msg-tokens">{{ $message->tokens }} tok</span>
                                @endif
                            </div>
                        </div>
                    </div>

                @elseif($message->role === 'assistant')
                    <div class="msg-row assistant">
                        <div class="msg-avatar avatar-assistant">AI</div>
                        <div>
                            <div class="msg-bubble bubble-assistant">{{ $message->message }}</div>
                            <div class="msg-meta">
                                {{ $message->created_at->format('H:i') }}
                                @if($message->tokens)
                                    <span class="msg-tokens">{{ $message->tokens }} tok</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

            @endforeach
        </div>
        @endif
    </div>

</div>

@endsection
