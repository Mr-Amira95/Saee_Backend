@extends('admin.layouts.app')

@section('title', 'Support Desk')
@section('page-title', 'Support Tickets')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Support Center</span>
@endsection

@section('head')
<style>
    /* Make the content area itself a flex column so chat-layout can fill it */
    .content {
        overflow: hidden !important;
        padding: 16px !important;
        display: flex;
        flex-direction: column;
    }

    .chat-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        flex: 1;
        min-height: 0;
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        overflow: hidden;
        backdrop-filter: blur(8px);
    }

    /* ─── Ticket Sidebar ─── */
    .chat-sidebar {
        border-right: 1px solid var(--bdr);
        display: flex;
        flex-direction: column;
        background: rgba(6, 9, 23, 0.4);
        min-height: 0;
        overflow: hidden;
    }

    .chat-sidebar-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--bdr);
    }

    .chat-sidebar-head h3 {
        font-size: .88rem;
        font-weight: 700;
        color: var(--text-sub);
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .ticket-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
    }

    .ticket-list::-webkit-scrollbar { width: 4px; }
    .ticket-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.05); border-radius: 2px; }

    .ticket-item {
        display: block;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(255,255,255,.02);
        text-decoration: none;
        color: inherit;
        transition: background .15s;
    }

    .ticket-item:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .ticket-item.active {
        background: rgba(220, 38, 38, 0.08);
        border-left: 3px solid var(--red-lt);
    }

    .ticket-item-hd {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .ticket-no {
        font-size: .8rem;
        font-weight: 700;
        color: var(--red-lt);
    }

    .ticket-time {
        font-size: .7rem;
        color: var(--text-dim);
    }

    .ticket-title {
        font-size: .84rem;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .ticket-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: .72rem;
        color: var(--text-sub);
    }

    /* ─── Active Chat Window ─── */
    .chat-window {
        display: flex;
        flex-direction: column;
        background: rgba(8, 12, 30, 0.6);
        min-height: 0;
        overflow: hidden;
    }

    .chat-window-head {
        padding: 16px 24px;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(6, 9, 23, 0.2);
    }

    .chat-window-title h2 {
        font-size: 1.05rem;
        font-weight: 700;
    }

    .chat-window-title p {
        font-size: .78rem;
        color: var(--text-sub);
        margin-top: 2px;
    }

    .chat-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .chat-body::-webkit-scrollbar { width: 6px; }
    .chat-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 3px; }

    /* Message Bubbles */
    .msg-wrap {
        display: flex;
        flex-direction: column;
        max-width: 70%;
    }

    .msg-wrap.incoming {
        align-self: flex-start;
    }

    .msg-wrap.outgoing {
        align-self: flex-end;
    }

    .msg-sender {
        font-size: .7rem;
        font-weight: 600;
        color: var(--text-dim);
        margin-bottom: 4px;
        padding: 0 4px;
    }

    .msg-wrap.outgoing .msg-sender {
        text-align: right;
    }

    .msg-bubble {
        padding: 12px 16px;
        border-radius: 14px;
        font-size: .84rem;
        line-height: 1.5;
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
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
    }

    .msg-time {
        font-size: .68rem;
        color: var(--text-dim);
        margin-top: 4px;
        padding: 0 4px;
    }

    .msg-wrap.outgoing .msg-time {
        text-align: right;
    }

    .chat-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--bdr);
        background: rgba(6, 9, 23, 0.3);
    }

    .chat-form {
        display: flex;
        gap: 12px;
    }

    .chat-input {
        flex: 1;
        background: var(--in-bg);
        border: 1px solid var(--in-bdr);
        border-radius: 10px;
        padding: 12px 16px;
        color: #fff;
        font-family: inherit;
        font-size: .86rem;
        outline: none;
        resize: none;
        height: 44px;
        transition: border-color .2s;
    }

    .chat-input:focus {
        border-color: rgba(220,38,38,.45);
    }

    .chat-send-btn {
        background: linear-gradient(135deg, var(--red-deep), var(--red));
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0 20px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity .15s;
    }

    .chat-send-btn:hover {
        opacity: .95;
    }
</style>
@endsection

@section('content')
    <div class="chat-layout">
        
        {{-- Tickets Queue --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar-head" style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                <h3>Tickets Queue</h3>
                <a href="{{ route('admin.support.create') }}" class="btn-primary" style="padding: 4px 8px; font-size: .75rem; box-shadow: none; border-radius: 6px; text-decoration: none;">
                    + Open Ticket
                </a>
            </div>
            <div class="ticket-list">
                @forelse($tickets as $t)
                    <a href="{{ route('admin.support.index', ['ticket' => $t->ticket_number]) }}" class="ticket-item {{ $activeTicket && $activeTicket->id === $t->id ? 'active' : '' }}" data-ticket-id="{{ $t->id }}">
                        <div class="ticket-item-hd">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span class="ticket-no">{{ $t->ticket_number }}</span>
                                @if(isset($t->unread_messages_count) && $t->unread_messages_count > 0)
                                    <span class="sidebar-badge" style="background: var(--red); font-size: .62rem; padding: 1px 4px; min-width: 14px; line-height: 10px;">{{ $t->unread_messages_count }}</span>
                                @endif
                            </div>
                            <span class="ticket-time">{{ $t->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="ticket-title">{{ $t->title }}</div>
                        <div class="ticket-meta">
                            <span>By: {{ $t->user ? $t->user->name : 'Guest Recipient' }}</span>
                            <span class="badge {{ $t->status === 'resolved' ? 'badge-active' : 'badge-pending' }}" style="padding: 2px 6px; font-size:.65rem">
                                {{ strtoupper(str_replace('_', ' ', $t->status)) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-dim); font-size: .8rem;">
                        No support tickets submitted.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Active Conversation Panel --}}
        <div class="chat-window">
            @if($activeTicket)
                <div class="chat-window-head">
                    <div class="chat-window-title">
                        <h2>{{ $activeTicket->title }} (#{{ $activeTicket->ticket_number }})</h2>
                        <p>
                            @if($activeTicket->order)
                                Related Order: <a href="{{ route('admin.orders.show', $activeTicket->order) }}" style="color: var(--red-lt); font-weight:600">#{{ $activeTicket->order->order_number }}</a>
                            @endif
                        </p>
                    </div>
                    
                    <div style="display:flex; gap: 8px;">
@if($activeTicket->status !== 'resolved')
                            <form id="resolveForm" action="{{ route('admin.support.resolve', $activeTicket) }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                            <button type="button" class="btn-danger" style="padding: 6px 12px; font-size:.78rem"
                                onclick="document.getElementById('resolveConfirmModal').style.display='flex'">
                                Resolve Ticket
                            </button>
                        @else
                            <span class="badge badge-active" style="padding: 6px 12px; font-size: .78rem;">
                                <span class="badge-dot"></span> Resolved
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Message Logs Body --}}
                <div class="chat-body" id="chatBody">
                    @foreach($activeTicket->messages as $msg)
                        <div class="msg-wrap {{ $msg->sender_id === auth()->id() ? 'outgoing' : 'incoming' }}">
                            <div class="msg-sender">{{ $msg->sender_name }}</div>
                            <div class="msg-bubble">{{ $msg->message }}</div>
                            <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Chat Input Form --}}
                <div class="chat-footer">
                    @if($activeTicket->status !== 'resolved')
                        <form id="adminChatForm" action="{{ route('admin.support.send', $activeTicket) }}" method="POST" class="chat-form">
                            @csrf
                            <textarea id="adminChatInput" name="message" class="chat-input" placeholder="Type your support reply…" rows="1"></textarea>
                            <button type="submit" id="adminSendBtn" class="chat-send-btn">Send Reply</button>
                        </form>
                    @else
                        <div style="text-align: center; color: var(--text-dim); font-size: .8rem; font-style: italic; padding: 10px;">
                            This ticket has been resolved. Re-open by sending a WhatsApp trigger or creating a message.
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-state" style="margin: auto;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">💬</div>
                    <h3>Operations Support Center</h3>
                    <p>Select a ticket conversation from the queue sidebar to begin chatting with clients or drivers.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- Resolve Confirmation Modal --}}
    <div id="resolveConfirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#0c1230;border:1px solid var(--bdr);border-radius:16px;padding:28px 30px;max-width:400px;width:90%;text-align:center;">
            <div style="font-size:2.2rem;margin-bottom:14px;">✅</div>
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px;">Resolve this ticket?</h3>
            <p style="font-size:.84rem;color:var(--text-sub);margin-bottom:24px;line-height:1.6;">The client will be notified that their issue has been resolved. They won't be able to reply until the ticket is reopened.</p>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn-secondary" style="flex:1;" onclick="document.getElementById('resolveConfirmModal').style.display='none'">Cancel</button>
                <button type="button" class="btn-danger" style="flex:1;" onclick="document.getElementById('resolveForm').submit()">Yes, Resolve</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
(function () {
    const PUSHER_KEY    = '{{ config('broadcasting.connections.pusher.key') }}';
    const PUSHER_CLUSTER = '{{ config('broadcasting.connections.pusher.options.cluster') }}';
    const pusher  = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });

    // ── 1. Real-time new tickets in sidebar ──────────────────
    const ticketList = document.querySelector('.ticket-list');
    const channel    = pusher.subscribe('support-admin');

    channel.bind('message.sent', function (data) {
        // Update sidebar item timestamp when a message arrives on any ticket
        const link = ticketList.querySelector(`a[data-ticket-id="${data.ticket_id}"]`);
        if (link) {
            link.querySelector('.ticket-time').textContent = 'Just now';
            ticketList.prepend(link); // bubble to top
        }
    });

    channel.bind('ticket.created', function (data) {
        // Remove empty state if present
        const empty = ticketList.querySelector('div');
        if (empty) empty.remove();

        // Don't duplicate if ticket already in list
        if (ticketList.querySelector(`[data-ticket-id="${data.id}"]`)) return;

        const statusClass = data.status === 'resolved' ? 'badge-active' : 'badge-pending';
        const statusLabel = data.status.replace('_', ' ').toUpperCase();

        const a = document.createElement('a');
        a.href = `?ticket=${data.ticket_number}`;
        a.className = 'ticket-item';
        a.dataset.ticketId = data.id;
        a.innerHTML = `
            <div class="ticket-item-hd">
                <span class="ticket-no">${data.ticket_number}</span>
                <span class="ticket-time">Just now</span>
            </div>
            <div class="ticket-title">${data.title}</div>
            <div class="ticket-meta">
                <span>By: ${data.user_name}</span>
                <span class="badge ${statusClass}" style="padding:2px 6px;font-size:.65rem">${statusLabel}</span>
            </div>
        `;
        ticketList.prepend(a);
    });

@if($activeTicket)
    // ── 2. Real-time messages in active chat ─────────────────
    const chatBody    = document.getElementById('chatBody');
    const activeId    = {{ $activeTicket->id }};
    const currentUser = {{ auth()->id() }};

    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;

    const chatChannel = pusher.subscribe('support.' + activeId);
    chatChannel.bind('message.sent', function (data) {
        // Ignore own messages — already appended optimistically
        if (data.sender_id === currentUser) return;
        appendMessage(data, false);
    });

    // AJAX form submit (optimistic append for own messages)
    let adminSending = false;

    function handleFormSubmit(e) {
        e.preventDefault();
        if (adminSending) return;
        const input   = document.getElementById('adminChatInput');
        const message = input.value.trim();
        if (!message) return;

        adminSending = true;
        const btn = document.getElementById('adminSendBtn');
        if (btn) btn.disabled = true;
        input.value = '';

        fetch("{{ route('admin.support.send', $activeTicket) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ message })
        })
        .then(r => r.json())
        .then(data => { if (data.success) appendMessage(data.message, true); })
        .catch(() => {})
        .finally(() => {
            adminSending = false;
            if (btn) btn.disabled = false;
        });
    }

    const adminForm  = document.getElementById('adminChatForm');
    const adminInput = document.getElementById('adminChatInput');
    if (adminForm)  adminForm.addEventListener('submit', handleFormSubmit);
    if (adminInput) {
        adminInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleFormSubmit(e);
            }
        });
    }

    function appendMessage(msg, isOutgoing) {
        const wrap = document.createElement('div');
        wrap.className = `msg-wrap ${isOutgoing ? 'outgoing' : 'incoming'}`;

        const d   = new Date(msg.created_at || msg.sent_at || new Date());
        const hh  = String(d.getHours()).padStart(2, '0');
        const mm  = String(d.getMinutes()).padStart(2, '0');

        wrap.innerHTML = `
            <div class="msg-sender">${msg.sender_name}</div>
            <div class="msg-bubble">${msg.message}</div>
            <div class="msg-time">${hh}:${mm}</div>
        `;
        chatBody.appendChild(wrap);
        chatBody.scrollTop = chatBody.scrollHeight;
    }
@endif

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.getElementById('resolveConfirmModal').style.display = 'none';
    });
})();
</script>
@endsection
