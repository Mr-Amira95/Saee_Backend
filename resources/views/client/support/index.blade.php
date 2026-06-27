@extends('client.layouts.app')
@section('title', 'Support')
@section('page-title', 'Support Tickets')

@push('styles')
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

    .toast-notif { position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid var(--bdr);color:var(--text);padding:12px 18px;border-radius:10px;font-size:.84rem;box-shadow:0 8px 24px rgba(0,0,0,.5);z-index:9999;animation:toastIn .2s ease; }
    @keyframes toastIn { from { opacity:0;transform:translateY(8px); } to { opacity:1;transform:translateY(0); } }
</style>
@endpush

@section('content')
    <div class="chat-layout">

        {{-- Tickets Queue --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar-head" style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                <h3>{{ __('My Tickets') }}</h3>
                <button type="button" class="btn-primary" onclick="document.getElementById('newTicketModal').style.display='flex'" style="padding: 4px 8px; font-size: .75rem; box-shadow: none; border-radius: 6px;">
                    + {{ __('Open Ticket') }}
                </button>
            </div>
            <div class="ticket-list">
                @forelse($tickets as $ticket)
                    <a href="{{ route('client.support.index', ['ticket' => $ticket->id]) }}"
                       class="ticket-item {{ $activeTicket && $activeTicket->id === $ticket->id ? 'active' : '' }}">
                        <div class="ticket-item-hd">
                            <span class="ticket-no">{{ $ticket->ticket_number }}</span>
                            <span class="ticket-time">{{ $ticket->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="ticket-title">{{ $ticket->title }}</div>
                        <div class="ticket-meta">
                            <span></span>
                            <span class="badge {{ $ticket->status === 'resolved' ? 'badge-active' : 'badge-pending' }}" style="padding: 2px 6px; font-size:.65rem">
                                {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-dim); font-size: .8rem;">
                        {{ __('No tickets yet.') }}
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
                                {{ __('Related Order') }}: <a href="{{ route('client.orders.show', $activeTicket->order) }}" style="color: var(--red-lt); font-weight:600">#{{ $activeTicket->order->order_number }}</a>
                            @endif
                        </p>
                    </div>

                    <div style="display:flex; gap: 8px;">
                        @if($activeTicket->status !== 'resolved')
                            <form id="closeTicketForm" method="POST" action="{{ route('client.support.close', $activeTicket->id) }}" style="display:none;">
                                @csrf
                            </form>
                            <button type="button" class="btn-danger" style="padding: 6px 12px; font-size:.78rem"
                                onclick="document.getElementById('closeConfirmModal').style.display='flex'">
                                {{ __('Close Ticket') }}
                            </button>
                        @else
                            <span class="badge badge-active" style="padding: 6px 12px; font-size: .78rem;">
                                <span class="badge-dot"></span> {{ __('Resolved') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Message Logs Body --}}
                <div class="chat-body" id="chatBody">
                    @foreach($activeTicket->messages as $msg)
                        @php $isMine = $msg->sender_id === auth()->id(); @endphp
                        <div class="msg-wrap {{ $isMine ? 'outgoing' : 'incoming' }}" data-id="{{ $msg->id }}">
                            <div class="msg-sender">{{ $msg->sender_name }}</div>
                            <div class="msg-bubble">{{ $msg->message }}</div>
                            <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Chat Input Form --}}
                <div class="chat-footer">
                    @if($activeTicket->status !== 'resolved')
                        <div class="chat-form">
                            <textarea id="msgInput" class="chat-input" placeholder="{{ __('Type your message…') }}" rows="1"
                                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
                            <button type="button" class="chat-send-btn" onclick="sendMessage()">{{ __('Send Reply') }}</button>
                        </div>
                    @else
                        <div style="text-align: center; color: var(--text-dim); font-size: .8rem; font-style: italic; padding: 10px;">
                            {{ __('This ticket is resolved. Open a new ticket if you need further assistance.') }}
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-state" style="margin: auto;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">💬</div>
                    <h3>{{ __('Support Center') }}</h3>
                    <p>{{ __('Select a ticket from the sidebar or open a new one to get help from our operations team.') }}</p>
                </div>
            @endif
        </div>

    </div>

    {{-- Close Ticket Confirmation Modal --}}
    <div id="closeConfirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#0c1230;border:1px solid var(--bdr);border-radius:16px;padding:28px 30px;max-width:400px;width:90%;text-align:center;">
            <div style="font-size:2.2rem;margin-bottom:14px;">🔒</div>
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px;">{{ __('Close this ticket?') }}</h3>
            <p style="font-size:.84rem;color:var(--text-sub);margin-bottom:24px;line-height:1.6;">{{ __('Once closed, you won\'t be able to send more replies. Open a new ticket if you need further help.') }}</p>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn-secondary" style="flex:1;" onclick="document.getElementById('closeConfirmModal').style.display='none'">{{ __('Cancel') }}</button>
                <button type="button" class="btn-danger" style="flex:1;" onclick="document.getElementById('closeTicketForm').submit()">{{ __('Yes, Close') }}</button>
            </div>
        </div>
    </div>

    {{-- New Ticket Modal --}}
    <div id="newTicketModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#0c1230;border:1px solid var(--bdr);border-radius:16px;padding:28px 30px;max-width:480px;width:90%;animation:fu .25s both;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h3 style="font-size:1rem;font-weight:700;">{{ __('Open New Ticket') }}</h3>
                <button onclick="document.getElementById('newTicketModal').style.display='none'" style="background:none;border:none;color:var(--text-dim);cursor:pointer;padding:4px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('client.support.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ __('Title *') }}</label>
                    <input name="title" type="text" class="form-input" placeholder="{{ __('Briefly describe your issue') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Related Order') }} <span style="color:var(--text-dim);font-weight:400;">{{ __('[optional]') }}</span></label>
                    <div style="position:relative;" id="orderDropdownWrap">
                        <div id="orderTrigger" onclick="toggleOrderDrop()"
                             style="display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:var(--in-bg);border:1px solid var(--in-bdr);border-radius:9px;cursor:pointer;transition:border-color .2s;user-select:none;">
                            <span id="orderTriggerLabel" style="font-size:.87rem;color:var(--text-dim);">{{ __('Select an order…') }}</span>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0;transition:transform .2s;" id="orderChevron"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <input type="hidden" name="order_id" id="orderIdInput">
                        <div id="orderDropList" style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:#0c1230;border:1px solid var(--bdr);border-radius:10px;z-index:600;box-shadow:0 8px 32px rgba(0,0,0,.55);overflow:hidden;">
                            <div style="padding:8px 10px;border-bottom:1px solid var(--bdr);">
                                <input type="text" id="orderSearch" autocomplete="off"
                                       placeholder="{{ __('Search by ref. number, name, or phone…') }}"
                                       oninput="filterOrders(this.value)"
                                       style="width:100%;box-sizing:border-box;padding:7px 10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:7px;color:var(--text);font-size:.83rem;font-family:inherit;outline:none;">
                            </div>
                            <div id="orderOptions" style="max-height:200px;overflow-y:auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Message *') }}</label>
                    <textarea name="message" class="form-textarea" placeholder="{{ __('Describe your issue in detail…') }}" required></textarea>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" class="btn-secondary" style="flex:1;" onclick="document.getElementById('newTicketModal').style.display='none'">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-primary" style="flex:1;">{{ __('Submit Ticket') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
@if($activeTicket)
const TICKET_ID  = {{ $activeTicket->id }};
const MY_USER_ID = {{ auth()->id() }};
let lastMsgId    = {{ $activeTicket->messages->last()?->id ?? 0 }};
let clientSending = false;

const chatBody = document.getElementById('chatBody');
if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;

function sendMessage() {
    if (clientSending) return;
    const input = document.getElementById('msgInput');
    const text  = input.value.trim();
    if (!text) return;

    clientSending = true;
    const btn = document.querySelector('.chat-send-btn');
    if (btn) btn.disabled = true;

    appendMessage({ sender_name: '{{ __('You') }}', message: text, sent_at: new Date().toISOString() }, true);
    input.value = '';

    fetch(`{{ route('client.support.message', ['ticket' => $activeTicket->id]) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: text })
    }).then(r => r.json()).then(data => {
        lastMsgId = data.id;
    }).catch(() => {})
    .finally(() => {
        clientSending = false;
        if (btn) btn.disabled = false;
    });
}

function appendMessage(msg, isOutgoing) {
    const wrap = document.createElement('div');
    wrap.className = `msg-wrap ${isOutgoing ? 'outgoing' : 'incoming'}`;
    if (msg.id) wrap.dataset.id = msg.id;

    const d  = new Date(msg.created_at || msg.sent_at || new Date());
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');

    wrap.innerHTML = `
        <div class="msg-sender">${escHtml(msg.sender_name)}</div>
        <div class="msg-bubble">${escHtml(msg.message)}</div>
        <div class="msg-time">${hh}:${mm}</div>
    `;
    chatBody.appendChild(wrap);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}

function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'toast-notif';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

// Poll for new messages every 4 seconds
setInterval(() => {
    fetch(`{{ route('client.support.messages', ['ticket' => $activeTicket->id]) }}?after=${lastMsgId}`, {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(msgs => {
        if (!msgs.length) return;
        let hasNew = false;
        msgs.forEach(m => {
            lastMsgId = Math.max(lastMsgId, m.id);
            if (m.sender_id !== MY_USER_ID) {
                appendMessage(m, false);
                hasNew = true;
            }
        });
        if (hasNew) showToast('{{ __('New message from support') }}');
    }).catch(() => {});
}, 4000);
@endif

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('newTicketModal').style.display = 'none';
        document.getElementById('closeConfirmModal').style.display = 'none';
        closeOrderDrop();
    }
});

// Searchable order dropdown
@php
$ordersJson = $orders->map(fn($o) => ['id' => $o->id, 'number' => $o->order_number, 'receiver' => $o->receiver?->receiver_name ?? '', 'phone' => $o->receiver?->receiver_phone ?? '', 'status' => $o->status]);
@endphp
const ORDERS = @json($ordersJson);
let orderDropOpen = false;

function renderOrders(list) {
    const el = document.getElementById('orderOptions');
    const none = `<div onmousedown="selectOrder('','{{ __('— None —') }}')"
        style="padding:9px 14px;font-size:.83rem;color:var(--text-dim);cursor:pointer;border-bottom:1px solid rgba(255,255,255,.04);"
        onmouseover="this.style.background='rgba(255,255,255,.04)'" onmouseout="this.style.background=''">{{ __('— None —') }}</div>`;
    if (!list.length) {
        el.innerHTML = none + '<div style="padding:10px 14px;font-size:.83rem;color:var(--text-dim);">No orders found</div>';
        return;
    }
    el.innerHTML = none + list.map(o => `
        <div onmousedown="selectOrder(${o.id}, '#${escHtml(o.number)}')"
             style="padding:9px 14px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.04);transition:background .1s;"
             onmouseover="this.style.background='rgba(255,255,255,.04)'" onmouseout="this.style.background=''">
            <div style="font-size:.83rem;font-weight:700;font-family:monospace;color:var(--text);">${escHtml(o.number)}</div>
            <div style="font-size:.75rem;color:var(--text-dim);margin-top:2px;display:flex;gap:8px;">
                <span>${escHtml(o.receiver)}</span>
                ${o.phone ? `<span>·</span><span>${escHtml(o.phone)}</span>` : ''}
                <span>·</span><span style="text-transform:capitalize;">${escHtml(o.status)}</span>
            </div>
        </div>`).join('');
}

function openOrderDrop() {
    if (orderDropOpen) return;
    orderDropOpen = true;
    document.getElementById('orderDropList').style.display = 'block';
    document.getElementById('orderTrigger').style.borderColor = 'rgba(220,38,38,.35)';
    document.getElementById('orderChevron').style.transform = 'rotate(180deg)';
    document.getElementById('orderSearch').value = '';
    renderOrders(ORDERS);
    setTimeout(() => document.getElementById('orderSearch').focus(), 30);
}

function closeOrderDrop() {
    if (!orderDropOpen) return;
    orderDropOpen = false;
    document.getElementById('orderDropList').style.display = 'none';
    document.getElementById('orderTrigger').style.borderColor = '';
    document.getElementById('orderChevron').style.transform = '';
}

function toggleOrderDrop() {
    orderDropOpen ? closeOrderDrop() : openOrderDrop();
}

function filterOrders(q) {
    const term = q.toLowerCase().trim();
    const filtered = term ? ORDERS.filter(o =>
        o.number.toLowerCase().includes(term) ||
        o.receiver.toLowerCase().includes(term) ||
        o.phone.toLowerCase().includes(term)
    ) : ORDERS;
    renderOrders(filtered);
}

function selectOrder(id, label) {
    document.getElementById('orderIdInput').value = id;
    const lbl = document.getElementById('orderTriggerLabel');
    lbl.textContent = label || '{{ __('Select an order…') }}';
    lbl.style.color = id ? 'var(--text)' : 'var(--text-dim)';
    closeOrderDrop();
}

document.addEventListener('click', e => {
    if (!document.getElementById('orderDropdownWrap').contains(e.target)) closeOrderDrop();
});
</script>
@endpush
