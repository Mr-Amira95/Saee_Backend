@extends('client.layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order Details')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
        <div>
            <div style="font-family:monospace;font-size:.9rem;color:var(--red-lt);font-weight:700;">{{ $order->order_number }}</div>
            <div style="font-size:.78rem;color:var(--text-dim);">Created {{ $order->created_at->format('d M Y, H:i') }}</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        @php
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
        <span class="badge {{ $statusClass }}" style="font-size:.8rem;padding:5px 12px;"><span class="badge-dot"></span>{{ $statusLabel }}</span>
        @if($order->status === 'pending')
        <button type="button" class="btn-danger" style="padding:7px 14px;font-size:.82rem;" onclick="document.getElementById('deleteModal').style.display='flex';">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete Order
        </button>
        @endif
    </div>
</div>

<div class="grid-2" style="align-items:start;gap:16px;">

    {{-- Left: Shipment & Receiver --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div style="font-size:.74rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;">Shipment Information</div>
            @if($order->order_description)
            <div style="margin-bottom:14px;">
                <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Contents / Description</div>
                <div style="font-size:.9rem;color:var(--text);">{{ $order->order_description }}</div>
            </div>
            @endif
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Payment Type</div>
                    <span class="badge {{ $order->payment_type === 'cod' ? 'badge-cod' : 'badge-prepaid' }}">{{ strtoupper($order->payment_type) }}</span>
                </div>
                @if($order->payment_type === 'cod' && $order->order_price)
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">COD Amount</div>
                    <div style="font-size:1.05rem;font-weight:700;color:#fbbf24;">{{ number_format($order->order_price, 2) }} JD</div>
                </div>
                @endif
                @if($order->delivery_on_customer)
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Delivery Charges On</div>
                    <span class="badge badge-info">Customer</span>
                </div>
                @if($order->delivery_customer_amount)
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Customer Delivery Fee</div>
                    <div style="font-size:.9rem;font-weight:600;">{{ number_format($order->delivery_customer_amount, 2) }} JD</div>
                </div>
                @endif
                @endif
                @if($order->batch_number)
                <div style="grid-column:1/-1;">
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Batch #</div>
                    <div style="font-family:monospace;font-size:.83rem;color:var(--text-sub);">{{ $order->batch_number }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div style="font-size:.74rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;">Receiver</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Name</div>
                    <div style="font-size:.9rem;font-weight:600;">{{ $order->receiver_name }}</div>
                </div>
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Phone</div>
                    <div style="font-size:.9rem;">{{ $order->receiver_phone }}</div>
                </div>
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">City</div>
                    <div style="font-size:.9rem;">{{ optional($order->city)->name }}</div>
                </div>
                <div>
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Area</div>
                    <div style="font-size:.9rem;">{{ optional($order->area)->name }}</div>
                </div>
                <div style="grid-column:1/-1;">
                    <div style="font-size:.74rem;color:var(--text-dim);margin-bottom:4px;">Address</div>
                    <div style="font-size:.88rem;line-height:1.5;">{{ $order->address_text }}</div>
                </div>
            </div>
        </div>

        @if($order->notes)
        <div class="card">
            <div style="font-size:.74rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">Special Instructions</div>
            <div style="font-size:.88rem;line-height:1.6;color:var(--text-sub);">{{ $order->notes }}</div>
        </div>
        @endif
    </div>

    {{-- Right: Tracking timeline --}}
    <div class="card">
        <div style="font-size:.74rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px;">Tracking History</div>

        @if($order->trackingLogs->isEmpty())
            <div style="text-align:center;padding:24px;color:var(--text-dim);font-size:.85rem;">No tracking events yet.</div>
        @else
        <div style="position:relative;">
            {{-- Vertical line --}}
            <div style="position:absolute;left:14px;top:8px;bottom:8px;width:2px;background:rgba(255,255,255,.06);border-radius:1px;"></div>

            @foreach($order->trackingLogs as $log)
            @php
                $isLast  = $loop->last;
                $isDone  = in_array($log->to_status ?? '', ['delivered', 'rejected', 'returned', 'cancelled']);
            @endphp
            <div style="display:flex;gap:16px;margin-bottom:{{ $loop->last ? '0' : '20px' }};position:relative;z-index:1;">
                <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;
                    {{ $isLast ? 'background:rgba(220,38,38,.15);border:2px solid rgba(220,38,38,.4);' : 'background:rgba(34,197,94,.08);border:2px solid rgba(34,197,94,.25);' }}">
                    @if($isDone && $log->to_status === 'delivered')
                        <svg width="11" height="11" fill="none" stroke="#4ade80" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @elseif($isDone)
                        <svg width="11" height="11" fill="none" stroke="#f87171" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        <div style="width:6px;height:6px;border-radius:50%;background:{{ $isLast ? 'var(--red)' : 'rgba(34,197,94,.7)' }};"></div>
                    @endif
                </div>
                <div style="flex:1;padding-top:4px;">
                    <div style="font-size:.86rem;font-weight:600;color:var(--text);">{{ $log->description }}</div>
                    @if($log->from_status && $log->to_status)
                    <div style="font-size:.76rem;color:var(--text-dim);margin-top:2px;">
                        <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $log->from_status) }}</span>
                        → <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $log->to_status) }}</span>
                    </div>
                    @endif
                    <div style="font-size:.74rem;color:var(--text-dim);margin-top:3px;">
                        {{ $log->created_at->format('d M Y, H:i') }}
                        @if($log->user) · {{ $log->user->name }} @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($order->driver)
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--bdr);">
            <div style="font-size:.74rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;">Assigned Driver</div>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--red-dark),var(--red));display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:white;flex-shrink:0;">{{ strtoupper(substr($order->driver->name,0,1)) }}</div>
                <div>
                    <div style="font-size:.88rem;font-weight:600;">{{ $order->driver->name }}</div>
                    <div style="font-size:.78rem;color:var(--text-dim);">{{ $order->driver->phone }}</div>
                </div>
            </div>
        </div>
        @endif
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
