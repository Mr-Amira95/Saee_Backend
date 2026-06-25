@extends('client.layouts.app')
@section('title', 'Support')
@section('page-title', 'Support')

@push('styles')
<style>
    .support-shell { display: flex; height: calc(100vh - 58px - 48px); gap: 0; border-radius: 14px; overflow: hidden; border: 1px solid var(--bdr); background: var(--card); }
    .ticket-sidebar { width: 300px; flex-shrink: 0; border-right: 1px solid var(--bdr); display: flex; flex-direction: column; overflow: hidden; }
    .ticket-sidebar-head { padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .ticket-sidebar-head h3 { font-size: .9rem; font-weight: 700; }
    .ticket-list { flex: 1; overflow-y: auto; }
    .ticket-list::-webkit-scrollbar { width: 4px; }
    .ticket-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 2px; }
    .ticket-item { padding: 13px 16px; border-bottom: 1px solid rgba(255,255,255,.03); cursor: pointer; transition: background .12s; text-decoration: none; display: block; }
    .ticket-item:hover { background: rgba(255,255,255,.025); }
    .ticket-item.active { background: rgba(220,38,38,.06); border-left: 2px solid var(--red); }
    .ticket-item-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; }
    .ticket-num { font-size: .72rem; color: var(--red-lt); font-weight: 700; font-family: monospace; }
    .ticket-time { font-size: .7rem; color: var(--text-dim); }
    .ticket-title { font-size: .83rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-sub); }
    .ticket-item.active .ticket-title { color: var(--text); }
    .ticket-sender { font-size: .73rem; color: var(--text-dim); margin-top: 3px; }

    .chat-area { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
    .chat-head { padding: 14px 18px; border-bottom: 1px solid var(--bdr); flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; }
    .chat-head-info h4 { font-size: .92rem; font-weight: 700; }
    .chat-head-meta { font-size: .76rem; color: var(--text-dim); margin-top: 2px; }
    .chat-messages { flex: 1; overflow-y: auto; padding: 18px; display: flex; flex-direction: column; gap: 12px; }
    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.07); border-radius: 2px; }
    .msg-bubble { max-width: 70%; }
    .msg-bubble.mine { align-self: flex-end; }
    .msg-bubble.theirs { align-self: flex-start; }
    .msg-inner { padding: 10px 14px; border-radius: 14px; font-size: .85rem; line-height: 1.5; }
    .msg-bubble.mine   .msg-inner { background: linear-gradient(135deg, var(--red-deep), var(--red)); color: white; border-bottom-right-radius: 4px; }
    .msg-bubble.theirs .msg-inner { background: rgba(255,255,255,.06); color: var(--text); border-bottom-left-radius: 4px; }
    .msg-meta { font-size: .71rem; color: var(--text-dim); margin-top: 4px; }
    .msg-bubble.mine   .msg-meta { text-align: right; }
    .msg-bubble.theirs .msg-meta { text-align: left; }
    .chat-footer { padding: 14px 16px; border-top: 1px solid var(--bdr); flex-shrink: 0; display: flex; gap: 10px; align-items: flex-end; }
    .chat-input { flex: 1; resize: none; padding: 10px 13px; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 10px; color: var(--text); font-size: .87rem; font-family: inherit; outline: none; transition: border-color .2s; max-height: 120px; min-height: 40px; }
    .chat-input:focus { border-color: rgba(220,38,38,.35); }
    .chat-send-btn { width: 38px; height: 38px; border-radius: 9px; background: var(--red); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: white; transition: opacity .15s; flex-shrink: 0; }
    .chat-send-btn:hover { opacity: .85; }
    .chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-dim); gap: 10px; }
    .status-open     { color: #4ade80; background: rgba(34,197,94,.1); }
    .status-resolved { color: #94a3b8; background: rgba(148,163,184,.1); }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
    <div>
        <h1 style="font-size:1.35rem;font-weight:800;">{{ __('Support') }}</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ __('Get help from the Saee operations team') }}</p>
    </div>
    <button type="button" class="btn-primary" onclick="document.getElementById('newTicketModal').style.display='flex'">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        {{ __('Open Ticket') }}
    </button>
</div>

<div class="support-shell">
    {{-- Ticket sidebar --}}
    <div class="ticket-sidebar">
        <div class="ticket-sidebar-head">
            <h3>{{ __('Tickets') }}</h3>
            <span style="font-size:.75rem;color:var(--text-dim);">{{ $tickets->count() }}</span>
        </div>
        <div class="ticket-list" id="ticketList">
            @forelse($tickets as $ticket)
            <a href="{{ route('client.support.index', ['ticket' => $ticket->id]) }}"
               class="ticket-item {{ $activeTicket && $activeTicket->id === $ticket->id ? 'active' : '' }}">
                <div class="ticket-item-header">
                    <span class="ticket-num">{{ $ticket->ticket_number }}</span>
                    <span class="ticket-time">{{ $ticket->updated_at->diffForHumans(null, true) }}</span>
                </div>
                <div class="ticket-title">{{ $ticket->title }}</div>
                <div class="ticket-sender">
                    <span class="badge {{ $ticket->status === 'open' ? 'status-open' : 'status-resolved' }}" style="font-size:.67rem;padding:2px 7px;">{{ ucfirst($ticket->status) }}</span>
                </div>
            </a>
            @empty
            <div style="padding:32px 16px;text-align:center;color:var(--text-dim);font-size:.84rem;">{{ __('No tickets yet.') }}</div>
            @endforelse
        </div>
    </div>

    {{-- Chat area --}}
    <div class="chat-area">
        @if($activeTicket)
        <div class="chat-head">
            <div class="chat-head-info">
                <h4>{{ $activeTicket->title }}</h4>
                <div class="chat-head-meta">
                    {{ $activeTicket->ticket_number }} &middot;
                    <span class="badge {{ $activeTicket->status === 'open' ? 'status-open' : 'status-resolved' }}" style="font-size:.68rem;padding:2px 7px;">{{ ucfirst($activeTicket->status) }}</span>
                </div>
            </div>
            @if($activeTicket->status === 'open')
            <form method="POST" action="{{ route('client.support.close', $activeTicket->id) }}"
                  onsubmit="return confirm('{{ __('Close this ticket? You won\'t be able to reply after closing.') }}')">
                @csrf
                <button type="submit" class="btn-secondary" style="padding:6px 14px;font-size:.8rem;display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ __('Close Ticket') }}
                </button>
            </form>
            @endif
        </div>

        <div class="chat-messages" id="chatMessages">
            @foreach($activeTicket->messages as $msg)
            @php $isMine = $msg->sender_id === auth()->id(); @endphp
            <div class="msg-bubble {{ $isMine ? 'mine' : 'theirs' }}" data-id="{{ $msg->id }}">
                <div class="msg-inner">{{ $msg->message }}</div>
                <div class="msg-meta">{{ $msg->sender_name }} · {{ $msg->created_at->format('H:i') }}</div>
            </div>
            @endforeach
        </div>

        @if($activeTicket->status === 'open')
        <div class="chat-footer">
            <textarea class="chat-input" id="msgInput" placeholder="{{ __('Type a message…') }}" rows="1"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
            <button class="chat-send-btn" onclick="sendMessage()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
        @else
        <div style="padding:14px 16px;text-align:center;font-size:.83rem;color:var(--text-dim);border-top:1px solid var(--bdr);">
            {{ __('This ticket is resolved. Open a new ticket if you need further assistance.') }}
        </div>
        @endif

        @else
        <div class="chat-empty">
            <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <div>{{ __('Select a ticket or open a new one') }}</div>
        </div>
        @endif
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
                    {{-- Selected display / trigger --}}
                    <div id="orderTrigger" onclick="toggleOrderDrop()"
                         style="display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:var(--in-bg);border:1px solid var(--in-bdr);border-radius:9px;cursor:pointer;transition:border-color .2s;user-select:none;">
                        <span id="orderTriggerLabel" style="font-size:.87rem;color:var(--text-dim);">{{ __('Select an order…') }}</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0;transition:transform .2s;" id="orderChevron"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <input type="hidden" name="order_id" id="orderIdInput">

                    {{-- Dropdown panel --}}
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
const TICKET_ID = {{ $activeTicket->id }};
const MY_USER_ID = {{ auth()->id() }};
let lastMsgId = {{ $activeTicket->messages->last()?->id ?? 0 }};

let clientSending = false;

function sendMessage() {
    if (clientSending) return;
    const input = document.getElementById('msgInput');
    const text  = input.value.trim();
    if (!text) return;

    clientSending = true;
    const btn = document.querySelector('.chat-send-btn');
    if (btn) btn.disabled = true;

    const temp = document.createElement('div');
    temp.className = 'msg-bubble mine';
    temp.innerHTML = `<div class="msg-inner">${escHtml(text)}</div><div class="msg-meta">{{ __('You') }} · {{ __('now') }}</div>`;
    document.getElementById('chatMessages').appendChild(temp);
    scrollToBottom();
    input.value = '';

    fetch(`{{ route('client.support.message', ['ticket' => $activeTicket->id]) }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ message: text })
    }).then(r => r.json()).then(data => {
        lastMsgId = data.id;
        temp.querySelector('.msg-meta').textContent = `{{ __('You') }} · ${data.created_at}`;
    }).catch(() => {})
    .finally(() => {
        clientSending = false;
        if (btn) btn.disabled = false;
    });
}

function scrollToBottom() {
    const c = document.getElementById('chatMessages');
    c.scrollTop = c.scrollHeight;
}
scrollToBottom();

function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'); }

// Poll for new messages
setInterval(() => {
    fetch(`{{ route('client.support.messages', ['ticket' => $activeTicket->id]) }}?after=${lastMsgId}`, {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(msgs => {
        if (!msgs.length) return;
        const container = document.getElementById('chatMessages');
        msgs.forEach(m => {
            if (m.sender_id !== MY_USER_ID) {
                const el = document.createElement('div');
                el.className = 'msg-bubble theirs';
                el.dataset.id = m.id;
                el.innerHTML = `<div class="msg-inner">${escHtml(m.message)}</div><div class="msg-meta">${escHtml(m.sender_name)} · ${m.created_at}</div>`;
                container.appendChild(el);
                lastMsgId = m.id;
            }
        });
        scrollToBottom();
    }).catch(() => {});
}, 4000);
@endif

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('newTicketModal').style.display = 'none';
        closeOrderDrop();
    }
});

// Searchable order dropdown
@php
$ordersJson = $orders->map(fn($o) => ['id' => $o->id, 'number' => $o->order_number, 'receiver' => $o->receiver_name ?? '', 'phone' => $o->receiver_phone ?? '', 'status' => $o->status]);
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
