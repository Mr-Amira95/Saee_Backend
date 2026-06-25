@extends('client.layouts.app')
@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')

<div class="page-hd">
    <div class="page-hd-left">
        <h1>Orders</h1>
        <p>Manage and track all your shipments</p>
    </div>
    <div class="page-hd-right">
        <a href="{{ route('client.orders.import') }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Orders
        </a>
        <a href="{{ route('client.orders.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Order
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('client.orders.index') }}">
<div class="filter-bar">
    <div class="filter-search-wrap">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input name="q" type="text" class="filter-input" placeholder="Order #, receiver name, or phone…" value="{{ request('q') }}">
    </div>
    <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>In Transit</option>
        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
        <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
        <option value="returned"  {{ request('status') === 'returned'  ? 'selected' : '' }}>Returned</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    <select name="payment_type" class="filter-select">
        <option value="">All Types</option>
        <option value="cod"     {{ request('payment_type') === 'cod'     ? 'selected' : '' }}>COD</option>
        <option value="prepaid" {{ request('payment_type') === 'prepaid' ? 'selected' : '' }}>Prepaid</option>
    </select>
    <input type="date" name="from" class="filter-input" style="max-width:140px;padding:8px 10px;" value="{{ request('from') }}" title="From date">
    <input type="date" name="to"   class="filter-input" style="max-width:140px;padding:8px 10px;" value="{{ request('to') }}" title="To date">
    <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:.82rem;">Filter</button>
    @if(request()->anyFilled(['q','status','payment_type','from','to']))
        <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:8px 14px;font-size:.82rem;">Clear</a>
    @endif
</div>
</form>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Receiver</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>COD Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
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
                <tr>
                    <td><span style="font-family:monospace;font-size:.82rem;color:var(--red-lt);">{{ $order->order_number }}</span>
                        @if($order->batch_number)
                            <div class="cell-sub">{{ $order->batch_number }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="cell-main">{{ $order->receiver_name }}</div>
                        <div class="cell-sub">{{ $order->receiver_phone }}</div>
                    </td>
                    <td>
                        <div class="cell-main">{{ optional($order->city)->name }}</div>
                        <div class="cell-sub">{{ optional($order->area)->name }}</div>
                    </td>
                    <td><span class="badge {{ $order->payment_type === 'cod' ? 'badge-cod' : 'badge-prepaid' }}">{{ strtoupper($order->payment_type) }}</span></td>
                    <td>
                        @if($order->payment_type === 'cod' && $order->order_price)
                            <span style="font-weight:700;color:#fbbf24;">{{ number_format($order->order_price, 2) }} JD</span>
                        @else
                            <span style="color:var(--text-dim);">—</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $statusClass }}"><span class="badge-dot"></span>{{ $statusLabel }}</span></td>
                    <td><span style="font-size:.8rem;color:var(--text-dim);">{{ $order->created_at->format('d M Y') }}</span></td>
                    <td>
                        <div class="act-btns">
                            <a href="{{ route('client.orders.show', $order) }}" class="act-btn act-btn-view" title="View">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if($order->status === 'pending')
                            <button type="button" class="act-btn act-btn-del" title="Delete" onclick="confirmDelete({{ $order->id }}, '{{ $order->order_number }}')">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--text-dim);">
                        No orders found.
                        <a href="{{ route('client.orders.create') }}" style="color:var(--red-lt);text-decoration:none;"> Create your first order →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="pagination">
        {{ $orders->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

{{-- Delete confirmation modal --}}
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);z-index:500;display:none;align-items:center;justify-content:center;">
    <div style="background:#0c1230;border:1px solid var(--bdr);border-radius:16px;padding:28px 30px;max-width:400px;width:90%;animation:fu .25s both;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px;">Delete Order?</h3>
        <p style="font-size:.86rem;color:var(--text-sub);" id="deleteModalMsg">This action cannot be undone.</p>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button class="btn-secondary" style="flex:1;" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="flex:1;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" style="width:100%;">Delete</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(id, num) {
    document.getElementById('deleteModalMsg').textContent = `Delete order ${num}? This cannot be undone.`;
    document.getElementById('deleteForm').action = `/client/orders/${id}`;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
</script>
@endpush
