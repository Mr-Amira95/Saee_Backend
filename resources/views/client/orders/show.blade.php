@extends('client.layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('page-title', __('Order Details'))

@push('styles')
<style>
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
    .info-card { background:var(--card); border:1px solid var(--bdr); border-radius:14px; padding:22px; backdrop-filter:blur(8px); }
    .info-card-title { font-size:.74rem; font-weight:700; color:var(--text-dim); text-transform:uppercase; letter-spacing:.1em; margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid var(--bdr); }
    .info-rows { display:flex; flex-direction:column; gap:10px; }
    .info-row { display:flex; align-items:flex-start; gap:12px; }
    .info-row span { font-size:.78rem; color:var(--text-dim); min-width:155px; flex-shrink:0; padding-top:2px; }
    .info-row strong { font-size:.84rem; color:var(--text); flex:1; word-break:break-word; }
    .timeline { position:relative; padding-left:32px; margin-top:8px; }
    .timeline::before { content:''; position:absolute; left:9px; top:6px; bottom:6px; width:2px; background:rgba(255,255,255,.08); }
    .timeline-item { position:relative; margin-bottom:24px; }
    .timeline-item:last-child { margin-bottom:0; }
    .timeline-icon { position:absolute; left:-32px; top:2px; width:20px; height:20px; border-radius:50%; background:var(--bg-2); border:2px solid var(--text-dim); z-index:2; display:flex; align-items:center; justify-content:center; }
    .timeline-item.active .timeline-icon { border-color:var(--red-lt); background:var(--red-lt); box-shadow:0 0 10px var(--red-glow); }
    .timeline-item.completed .timeline-icon { border-color:var(--success); background:var(--success); }
    .timeline-time { font-size:.72rem; color:var(--text-dim); font-weight:600; }
    .timeline-title { font-size:.86rem; font-weight:700; color:var(--text); margin-top:2px; }
    .timeline-desc { font-size:.81rem; color:var(--text-sub); margin-top:3px; line-height:1.4; }
    .timeline-user { font-size:.74rem; color:var(--red-lt); font-weight:500; margin-top:4px; }
    @media (max-width:720px) {
        .info-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')

@php
    $payment  = $order->payment;
    $receiver = $order->receiver;
    $statusClass = match($order->status) {
        'pending'   => 'badge-pending',
        'picked_up' => 'badge-info',
        'delivered' => 'badge-success',
        'rejected'  => 'badge-danger',
        'returned'  => 'badge-neutral',
        'cancelled' => 'badge-neutral',
        default     => 'badge-neutral',
    };
    $statusLabel = match($order->status) {
        'pending'   => 'Pending',
        'picked_up' => 'In Transit',
        'delivered' => 'Delivered',
        'rejected'  => 'Rejected',
        'returned'  => 'Returned',
        'cancelled' => 'Cancelled',
        default     => ucfirst($order->status),
    };
@endphp

{{-- Page Header --}}
<div class="page-hd">
    <div class="page-hd-left" style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
        <div>
            <h1>Order #{{ $order->order_number }}</h1>
            <p>Created {{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>
    <div class="page-hd-right">
        <span class="badge {{ $statusClass }}" style="font-size:.8rem;padding:5px 12px;"><span class="badge-dot"></span>{{ $statusLabel }}</span>
        @if($order->status === 'pending')
        <a href="{{ route('client.orders.edit', $order) }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Order
        </a>
        <button type="button" class="btn-danger" style="padding:7px 14px;font-size:.82rem;" onclick="document.getElementById('deleteModal').style.display='flex'">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete Order
        </button>
        @endif
    </div>
</div>

{{-- Row 1: Shipment Info + Financial Details --}}
<div class="info-grid">
    {{-- Shipment Information --}}
    <div class="info-card">
        <div class="info-card-title">Shipment Information</div>
        <div class="info-rows">
            <div class="info-row">
                <span>Receiver Name:</span>
                <strong>{{ $receiver?->receiver_name }}</strong>
            </div>
            <div class="info-row">
                <span>Receiver Phone:</span>
                <strong>{{ $receiver?->receiver_phone }}</strong>
            </div>
            <div class="info-row">
                <span>City / Area:</span>
                <strong>{{ $receiver?->city?->name }} • {{ $receiver?->area?->name }}</strong>
            </div>
            <div class="info-row">
                <span>Address:</span>
                <strong>{{ $receiver?->address_text }}</strong>
            </div>
            <div class="info-row">
                <span>Description:</span>
                <strong>{{ $order->order_description ?: '—' }}</strong>
            </div>
            @if($order->notes)
            <div class="info-row">
                <span>Notes:</span>
                <strong>{{ $order->notes }}</strong>
            </div>
            @endif
            @if($order->batch_number)
            <div class="info-row">
                <span>Batch #:</span>
                <strong style="font-family:monospace;font-size:.83rem;color:var(--red-lt);">{{ $order->batch_number }}</strong>
            </div>
            @endif
        </div>
    </div>

    {{-- Financial Details --}}
    <div class="info-card">
        <div class="info-card-title">Financial Details</div>
        <div class="info-rows">
            <div class="info-row">
                <span>Payment Type:</span>
                <strong>
                    <span class="badge {{ $payment?->payment_type === 'cod' ? 'badge-cod' : 'badge-prepaid' }}" style="text-transform:uppercase;">
                        {{ $payment?->payment_type }}
                    </span>
                </strong>
            </div>
            @if($payment?->payment_type === 'cod')
            <div class="info-row">
                <span>Goods Price (COD):</span>
                <strong style="font-size:1.05rem;color:#fbbf24;">{{ number_format($payment->order_amount ?? 0, 2) }} JD</strong>
            </div>
            @endif
            <div class="info-row">
                <span>Shipping Fee:</span>
                <strong>{{ number_format($payment?->client_delivery_amount ?? 0, 2) }} JD</strong>
            </div>
            @if($payment?->delivery_on_customer)
            <div class="info-row">
                <span>Customer Delivery Fee:</span>
                <strong>{{ number_format($payment->customer_delivery_amount ?? 0, 2) }} JD</strong>
            </div>
            @endif
            <div class="info-row" style="border-top:1px solid var(--bdr);padding-top:10px;margin-top:4px;">
                <span>Total Cash to Collect:</span>
                @php
                    $totalCollect = ($payment?->payment_type === 'cod' ? (float)($payment->order_amount ?? 0) : 0)
                        + ($payment?->delivery_on_customer ? (float)($payment->customer_delivery_amount ?? 0) : 0);
                @endphp
                <strong style="font-size:1.15rem;font-weight:800;color:#22c55e;">{{ number_format($totalCollect, 2) }} JD</strong>
            </div>
            <div class="info-row">
                <span>Payment Status:</span>
                <strong>
                    <span class="badge {{ $order->payment_status === 'paid' ? 'badge-success' : 'badge-pending' }}">
                        <span class="badge-dot"></span>
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                </strong>
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Tracking + Proof --}}
<div class="info-grid">
    {{-- Tracking History --}}
    <div class="info-card">
        <div class="info-card-title">Tracking History</div>
        @if($order->trackingLogs->isEmpty())
            <div style="text-align:center;padding:24px 0;color:var(--text-dim);font-size:.85rem;">No tracking events yet.</div>
        @else
        <div class="timeline">
            @foreach($order->trackingLogs as $log)
            <div class="timeline-item {{ $loop->first ? 'active' : 'completed' }}">
                <div class="timeline-icon">
                    @if($log->to_status === 'delivered')
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    @elseif(in_array($log->to_status, ['rejected', 'cancelled']))
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    @else
                        <span class="badge-dot" style="margin:0"></span>
                    @endif
                </div>
                <div class="timeline-time">{{ $log->created_at->format('d M Y, H:i') }}</div>
                <div class="timeline-title">Status: <span style="color:var(--red-lt);">{{ ucfirst(str_replace('_', ' ', $log->to_status)) }}</span></div>
                <div class="timeline-desc">{{ $log->description }}</div>
                @if($log->user)
                <div class="timeline-user">Updated by: {{ $log->user->name }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Proof of Delivery / Notes --}}
    <div class="info-card">
        <div class="info-card-title">Proof of Delivery</div>
        <div class="info-rows" style="gap:16px;">
            @if($order->status === 'delivered')
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.45);letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px;">Customer Signature</div>
                    @if($order->signature_path)
                        <div style="background:white;border-radius:8px;padding:10px;display:inline-block;border:1px solid var(--bdr);">
                            <img src="{{ asset('storage/' . $order->signature_path) }}" alt="Signature" style="max-height:100px;width:auto;max-width:100%;">
                        </div>
                    @else
                        <span style="font-size:.82rem;color:var(--text-dim);font-style:italic;">No digital signature captured</span>
                    @endif
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.45);letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px;">Proof Photo</div>
                    @if($order->proof_image_path)
                        <div style="border-radius:8px;overflow:hidden;max-width:200px;border:1px solid var(--bdr);">
                            <img src="{{ asset('storage/' . $order->proof_image_path) }}" alt="Proof" style="width:100%;height:auto;">
                        </div>
                    @else
                        <span style="font-size:.82rem;color:var(--text-dim);font-style:italic;">No proof photo uploaded</span>
                    @endif
                </div>
            @elseif($order->status === 'rejected')
                <div>
                    <div style="font-size:.72rem;font-weight:700;color:rgba(220,38,38,.6);letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px;">Rejection Reason</div>
                    <strong style="color:#f87171;">{{ $order->rejectionReason?->reason ?? 'Not specified' }}</strong>
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:var(--text-dim);font-size:.85rem;">
                    Proof of delivery will appear here once the order is delivered.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Delete modal --}}
@if($order->status === 'pending')
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
    <div style="background:#0c1230;border:1px solid var(--bdr);border-radius:16px;padding:28px 30px;max-width:400px;width:90%;animation:fu .25s both;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px;">Delete Order?</h3>
        <p style="font-size:.86rem;color:var(--text-sub);">Delete order <strong>{{ $order->order_number }}</strong>? This cannot be undone.</p>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button class="btn-secondary" style="flex:1;" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
            <form method="POST" action="{{ route('client.orders.destroy', $order) }}" style="flex:1;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" style="width:100%;">Delete</button>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        const m = document.getElementById('deleteModal');
        if (m) m.style.display = 'none';
    }
});
</script>
@endpush
