@extends('admin.layouts.app')

@section('title', __('WhatsApp') . ' — #' . $order->order_number)
@section('page-title', __('WhatsApp Conversation'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.whatsapp-logs.index') }}">{{ __('WhatsApp Messages') }}</a>
    <span class="sep">/</span>
    <span class="current">#{{ $order->order_number }}</span>
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
        background: linear-gradient(135deg,#22c55e,#4ade80);
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 700; color: white;
    }
    .chat-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }

    .msg-row { display: flex; gap: 10px; }
    .msg-row.inbound { flex-direction: row-reverse; }

    .msg-avatar {
        width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; font-weight: 700; color: white;
        align-self: flex-end;
    }
    .msg-avatar.avatar-outbound { background: linear-gradient(135deg,#4f46e5,#818cf8); }
    .msg-avatar.avatar-inbound  { background: linear-gradient(135deg,#22c55e,#4ade80); }

    .msg-bubble {
        max-width: 72%; padding: 10px 14px; border-radius: 14px;
        font-size: .855rem; line-height: 1.6; word-break: break-word;
    }
    .msg-bubble.bubble-outbound {
        background: rgba(79,70,229,.1); border: 1px solid rgba(79,70,229,.18);
        border-bottom-left-radius: 4px; color: var(--text);
    }
    .msg-bubble.bubble-inbound {
        background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.18);
        border-bottom-right-radius: 4px; color: var(--text);
    }
    .msg-bubble a { color: inherit; }
    .msg-meta {
        font-size: .68rem; color: var(--text-dim);
        margin-top: 4px; display: flex; align-items: center; gap: 6px;
    }
    .msg-row.inbound .msg-meta { justify-content: flex-end; }
    .msg-type-badge {
        background: rgba(255,255,255,.05); border-radius: 4px;
        padding: 1px 5px; font-size: .63rem; color: var(--text-dim);
        text-transform: uppercase; letter-spacing: .04em;
    }

    .chat-empty { padding: 50px 20px; text-align: center; color: var(--text-dim); font-size: .85rem; }
</style>
@endsection

@section('content')

<div style="margin-bottom:16px">
    <a href="{{ route('admin.whatsapp-logs.index') }}" class="btn-secondary" style="padding:8px 14px;font-size:.82rem">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ __('Back') }}
    </a>
</div>

<div class="convo-layout">

    {{-- Meta sidebar --}}
    <div class="convo-meta">
        <div class="convo-meta-title">{{ __('Order Info') }}</div>

        <div class="meta-row">
            <span class="meta-lbl">{{ __('Order') }}</span>
            <a href="{{ route('admin.orders.show', $order) }}" class="meta-val" style="font-weight:600">#{{ $order->order_number }}</a>
            <span style="font-size:.74rem;color:var(--text-sub)">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
        </div>

        <div class="meta-row">
            <span class="meta-lbl">{{ __('Customer') }}</span>
            @if($order->receiver)
                <span class="meta-val" style="font-weight:600">{{ $order->receiver->receiver_name }}</span>
                <span style="font-size:.74rem;color:var(--text-sub)">{{ $order->receiver->receiver_phone }}</span>
            @else
                <span class="badge badge-no" style="width:fit-content">{{ __('No Receiver') }}</span>
            @endif
        </div>

        @if($order->driverProfile?->user)
        <div class="meta-row">
            <span class="meta-lbl">{{ __('Driver') }}</span>
            <span class="meta-val">{{ $order->driverProfile->user->name }}</span>
        </div>
        @endif

        <div class="meta-row">
            <span class="meta-lbl">{{ __('Messages') }}</span>
            <span class="meta-val" style="font-size:1.2rem;font-weight:800">{{ $order->whatsappLogs->count() }}</span>
        </div>

        @if($order->receiver?->location_received_at)
        <div class="meta-row">
            <span class="meta-lbl">{{ __('Location Shared') }}</span>
            <span class="meta-val">{{ $order->receiver->location_received_at->diffForHumans() }}</span>
            @if($order->receiver->receiver_latitude && $order->receiver->receiver_longitude)
                <a href="https://maps.google.com/?q={{ $order->receiver->receiver_latitude }},{{ $order->receiver->receiver_longitude }}" target="_blank" rel="noopener" style="font-size:.78rem">{{ __('Open in Maps') }}</a>
            @endif
        </div>
        @endif

        @php
            $outboundCount = $order->whatsappLogs->where('direction', 'outbound')->count();
            $inboundCount  = $order->whatsappLogs->where('direction', 'inbound')->count();
        @endphp
        <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap">
            <span style="font-size:.72rem;color:var(--text-sub)">
                <span style="color:#818cf8;font-weight:700">{{ $outboundCount }}</span> {{ __('sent') }}
            </span>
            <span style="font-size:.72rem;color:var(--text-sub)">
                <span style="color:#4ade80;font-weight:700">{{ $inboundCount }}</span> {{ __('received') }}
            </span>
        </div>
    </div>

    {{-- Chat thread --}}
    <div class="chat-card">
        <div class="chat-header">
            <div class="chat-header-icon">
                <svg width="16" height="16" fill="none" stroke="white" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
            </div>
            <div>
                <div style="font-size:.88rem;font-weight:700">{{ __('Conversation Thread') }}</div>
                <div style="font-size:.74rem;color:var(--text-sub)">{{ $order->whatsappLogs->count() }} {{ __('messages') }}</div>
            </div>
        </div>

        @if($order->whatsappLogs->isEmpty())
            <div class="chat-empty">{{ __('No WhatsApp messages for this order yet.') }}</div>
        @else
        <div class="chat-body">
            @foreach($order->whatsappLogs as $log)
                <div class="msg-row {{ $log->direction }}">
                    <div class="msg-avatar avatar-{{ $log->direction }}">
                        {{ $log->direction === 'inbound' ? 'C' : 'US' }}
                    </div>
                    <div>
                        <div class="msg-bubble bubble-{{ $log->direction }}">
                            @if($log->message_type === 'location' && $log->meta && !empty($log->meta['latitude']) && !empty($log->meta['longitude']))
                                📍 <a href="https://maps.google.com/?q={{ $log->meta['latitude'] }},{{ $log->meta['longitude'] }}" target="_blank" rel="noopener">{{ $log->message }}</a>
                            @else
                                {!! nl2br(e($log->message)) !!}
                            @endif
                        </div>
                        <div class="msg-meta">
                            {{ $log->created_at->format('d M, H:i') }}
                            <span class="msg-type-badge">{{ $log->message_type ?? 'text' }}</span>
                            @if($log->direction === 'outbound')
                                <span class="msg-type-badge">{{ $log->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

@endsection
