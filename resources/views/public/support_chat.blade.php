<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sa\'ee Logistics — Customer Support Desk') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red:      #dc2626;
            --red-lt:   #ef4444;
            --red-deep: #7f1d1d;
            --bg:       #080c1e;
            --bg-grad:  radial-gradient(circle at top, #0c1230 0%, #080c1e 100%);
            --card:     rgba(12,18,48,.8);
            --bdr:      rgba(255,255,255,.07);
            --text:     #f1f5f9;
            --text-sub: #94a3b8;
            --text-dim: #475569;
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            background-image: var(--bg-grad);
            color: var(--text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 520px;
            height: calc(100vh - 40px);
            background: var(--card);
            border: 1px solid var(--bdr);
            border-radius: 20px;
            backdrop-filter: blur(12px);
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 50px rgba(0,0,0,.5);
            animation: slide-up .5s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo-wrap {
            padding: 16px 20px;
            border-bottom: 1px solid var(--bdr);
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(6, 9, 23, 0.2);
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
        .logo-text {
            font-size: .95rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #fff;
        }
        .chat-header {
            padding: 16px 20px;
            background: rgba(255,255,255,0.01);
            border-bottom: 1px solid var(--bdr);
        }
        .chat-header h1 {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .chat-header p {
            font-size: .78rem;
            color: var(--text-sub);
        }

        /* ─── Chat Messages Area ─── */
        .chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .chat-body::-webkit-scrollbar { width: 4px; }
        .chat-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.05); border-radius: 2px; }

        .msg-wrap {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }
        .msg-wrap.incoming {
            align-self: flex-start;
        }
        .msg-wrap.outgoing {
            align-self: flex-end;
        }
        .msg-sender {
            font-size: .66rem;
            font-weight: 600;
            color: var(--text-dim);
            margin-bottom: 2px;
            padding: 0 4px;
        }
        .msg-wrap.outgoing .msg-sender {
            text-align: right;
        }
        .msg-bubble {
            padding: 10px 14px;
            border-radius: 12px;
            font-size: .82rem;
            line-height: 1.45;
            word-break: break-word;
        }
        .incoming .msg-bubble {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--bdr);
            color: var(--text-sub);
            border-top-left-radius: 2px;
        }
        .outgoing .msg-bubble {
            background: linear-gradient(135deg, var(--red-deep), var(--red));
            color: #fff;
            border-top-right-radius: 2px;
        }
        .msg-time {
            font-size: .64rem;
            color: var(--text-dim);
            margin-top: 3px;
            padding: 0 4px;
        }
        .msg-wrap.outgoing .msg-time {
            text-align: right;
        }

        /* ─── Footer Form ─── */
        .chat-footer {
            padding: 16px;
            border-top: 1px solid var(--bdr);
            background: rgba(6, 9, 23, 0.3);
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        .chat-form {
            display: flex;
            gap: 10px;
        }
        .chat-input {
            flex: 1;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--bdr);
            border-radius: 10px;
            padding: 10px 14px;
            color: #fff;
            font-family: inherit;
            font-size: .84rem;
            outline: none;
            resize: none;
            height: 40px;
        }
        .chat-input:focus {
            border-color: rgba(220,38,38,.45);
        }
        .chat-send-btn {
            background: linear-gradient(135deg, var(--red-deep), var(--red));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 600;
            font-size: .82rem;
            cursor: pointer;
            transition: opacity .15s;
        }
        .chat-send-btn:hover {
            opacity: .95;
        }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .msg-wrap.outgoing { align-self: flex-start; }
        html[dir="rtl"] .msg-wrap.incoming { align-self: flex-end; }
        html[dir="rtl"] .incoming .msg-bubble { border-top-left-radius: 12px; border-top-right-radius: 2px; }
        html[dir="rtl"] .outgoing .msg-bubble { border-top-right-radius: 12px; border-top-left-radius: 2px; }
        html[dir="rtl"] .msg-wrap.outgoing .msg-sender { text-align: left; }
        html[dir="rtl"] .msg-wrap.outgoing .msg-time { text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Logo --}}
        <div class="logo-wrap">
            <svg viewBox="0 0 200 200" width="22" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="95" fill="#dc2626"/>
                <path d="M22,158 C38,88 90,52 172,46 L171,68 C96,74 52,108 44,165 Z" fill="white"/>
            </svg>
            <span class="logo-text">Sa'ee Logistics</span>
        </div>

        {{-- Ticket Head --}}
        <div class="chat-header">
            <h1>{{ $ticket->title }}</h1>
            <p>
                {{ __('Ticket:') }} <strong>#{{ $ticket->ticket_number }}</strong> &bull;
                {{ __('Status:') }} <span style="font-weight:700; color: {{ $ticket->status === 'resolved' ? '#4ade80' : '#fcd34d' }}">{{ strtoupper(str_replace('_',' ',$ticket->status)) }}</span>
                @if($ticket->order)
                    &bull; {{ __('Order:') }} #{{ $ticket->order->order_number }}
                @endif
            </p>
        </div>

        {{-- Messages list --}}
        <div class="chat-body" id="chatBody">
            @foreach($ticket->messages as $msg)
                <div class="msg-wrap {{ $msg->sender_id && $msg->sender->role === 'superadmin' || $msg->sender && $msg->sender->role === 'admin' ? 'incoming' : 'outgoing' }}">
                    <div class="msg-sender">{{ $msg->sender_name }}</div>
                    <div class="msg-bubble">{{ $msg->message }}</div>
                    <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            @endforeach
        </div>

        {{-- Textarea input --}}
        <div class="chat-footer">
            @if($ticket->status !== 'resolved')
                <form id="publicChatForm" action="{{ route('public.support.send', $ticket->token) }}" method="POST" class="chat-form" onsubmit="handleFormSubmit(event)">
                    @csrf
                    <textarea id="publicChatInput" name="message" class="chat-input" placeholder="{{ __('Type your message to support desk...') }}" rows="1"></textarea>
                    <button type="submit" id="publicSendBtn" class="chat-send-btn">{{ __('Send') }}</button>
                </form>
            @else
                <div style="text-align: center; color: var(--text-dim); font-size: .8rem; font-style: italic;">
                    {{ __('This ticket has been marked as resolved by Sa\'ee Admin.') }}
                </div>
            @endif
        </div>
    </div>

    <script>
        // Scroll to bottom
        const chatBody = document.getElementById('chatBody');
        chatBody.scrollTop = chatBody.scrollHeight;

        // AJAX submit message
        let publicSending = false;

        function handleFormSubmit(e) {
            e.preventDefault();
            if (publicSending) return;
            const input = document.getElementById('publicChatInput');
            const msg = input.value.trim();
            if (!msg) return;

            publicSending = true;
            const btn = document.getElementById('publicSendBtn');
            if (btn) btn.disabled = true;
            input.value = '';

            fetch("{{ route('public.support.send', $ticket->token) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: msg })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    appendMessage(data.message, true);
                }
            })
            .catch(() => {})
            .finally(() => {
                publicSending = false;
                if (btn) btn.disabled = false;
            });
        }

        document.getElementById('publicChatInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleFormSubmit(e);
            }
        });

        // Append message
        function appendMessage(msg, isOutgoing) {
            const wrap = document.createElement('div');
            wrap.className = `msg-wrap ${isOutgoing ? 'outgoing' : 'incoming'}`;
            
            const date = new Date(msg.created_at || new Date());
            const timeStr = String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');

            wrap.innerHTML = `
                <div class="msg-sender">${msg.sender_name}</div>
                <div class="msg-bubble">${msg.message}</div>
                <div class="msg-time">${timeStr}</div>
            `;
            chatBody.appendChild(wrap);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Real-time polling
        let lastMessagesCount = {{ $ticket->messages->count() }};
        setInterval(() => {
            fetch("{{ route('public.support.messages', $ticket->token) }}")
            .then(res => res.json())
            .then(data => {
                if (data.success && data.messages.length > lastMessagesCount) {
                    chatBody.innerHTML = '';
                    data.messages.forEach(msg => {
                        // Check if sender is admin/superadmin to align left/right
                        const isAdmin = msg.sender_name.includes('(Operations)') || msg.sender_id && (msg.sender_id == 1); // fallback
                        appendMessage(msg, !isAdmin);
                    });
                    lastMessagesCount = data.messages.length;
                }
            });
        }, 3000);
    </script>
</body>
</html>
