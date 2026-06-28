@extends('client.layouts.app')
@section('title', __('Orders'))
@section('page-title', __('Orders'))

@section('content')

<div class="page-hd">
    <div class="page-hd-left">
        <h1>{{ __('Orders') }}</h1>
        <p>{{ __('Manage and track all your shipments') }}</p>
    </div>
    <div class="page-hd-right">
        <a href="{{ route('client.orders.print-all', request()->query()) }}" target="_blank" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            {{ __('Print Waybills') }}
        </a>
        <a href="{{ route('client.orders.export', request()->query()) }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            {{ __('Export CSV') }}
        </a>
        <a href="{{ route('client.orders.import') }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            {{ __('Import Orders') }}
        </a>
        <a href="{{ route('client.orders.import-image') }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('Import Image (AI)') }}
        </a>
        <a href="{{ route('client.orders.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ __('Add Order') }}
        </a>
    </div>
</div>

{{-- Statistics --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;animation:fu .5s both;">
    {{-- Pending orders --}}
    <div class="card" style="padding:18px 20px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <div style="width:32px;height:32px;border-radius:9px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('Pending Orders') }}</span>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#fbbf24;line-height:1;margin-bottom:5px;">{{ number_format($stats['pending']) }}</div>
        <div style="font-size:.72rem;color:var(--text-dim);">{{ __('Pending, in transit & rejected') }}</div>
    </div>

    {{-- Delivered orders --}}
    <div class="card" style="padding:18px 20px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <div style="width:32px;height:32px;border-radius:9px;background:rgba(34,197,94,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('Delivered') }}</span>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#4ade80;line-height:1;margin-bottom:5px;">{{ number_format($stats['delivered']) }}</div>
        <div style="font-size:.72rem;color:var(--text-dim);">{{ __('Successfully delivered') }}</div>
    </div>

    {{-- Returned orders --}}
    <div class="card" style="padding:18px 20px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <div style="width:32px;height:32px;border-radius:9px;background:rgba(148,163,184,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </div>
            <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('Returned') }}</span>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#94a3b8;line-height:1;margin-bottom:5px;">{{ number_format($stats['returned']) }}</div>
        <div style="font-size:.72rem;color:var(--text-dim);">{{ __('Returned to sender') }}</div>
    </div>

    {{-- Pending cash --}}
    <div class="card" style="padding:18px 20px;border-color:var(--bdr-red);background:rgba(220,38,38,.04);">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <div style="width:32px;height:32px;border-radius:9px;background:rgba(220,38,38,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span style="font-size:.69rem;font-weight:700;color:var(--text-dim);letter-spacing:.09em;text-transform:uppercase;">{{ __('Pending Cash') }}</span>
        </div>
        <div style="font-size:1.7rem;font-weight:800;color:#f87171;line-height:1;margin-bottom:5px;">{{ number_format($stats['pending_cash'], 2) }} <span style="font-size:1rem;font-weight:600;">JD</span></div>
        <div style="font-size:.72rem;color:var(--text-dim);">{{ __('With driver or company') }}</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('client.orders.index') }}">
<div class="filter-bar">
    <div class="filter-search-wrap">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input name="q" type="text" class="filter-input" placeholder="{{ __('Order #, receiver name, or phone…') }}" value="{{ request('q') }}">
    </div>
    <select name="status" class="filter-select">
        <option value="">{{ __('All Statuses') }}</option>
        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>{{ __('Pending') }}</option>
        <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>{{ __('In Transit') }}</option>
        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
        <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>{{ __('Rejected') }}</option>
        <option value="returned"  {{ request('status') === 'returned'  ? 'selected' : '' }}>{{ __('Returned') }}</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
    </select>
    <select name="payment_type" class="filter-select">
        <option value="">{{ __('All Types') }}</option>
        <option value="cod"     {{ request('payment_type') === 'cod'     ? 'selected' : '' }}>{{ __('COD') }}</option>
        <option value="prepaid" {{ request('payment_type') === 'prepaid' ? 'selected' : '' }}>{{ __('Prepaid') }}</option>
    </select>
    <input type="date" name="from" class="filter-input" style="max-width:140px;padding:8px 10px;" value="{{ request('from') }}" title="{{ __('From date') }}">
    <input type="date" name="to"   class="filter-input" style="max-width:140px;padding:8px 10px;" value="{{ request('to') }}" title="{{ __('To date') }}">
    <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:.82rem;">{{ __('Filter') }}</button>
    @if(request()->anyFilled(['q','status','payment_type','from','to']))
        <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:8px 14px;font-size:.82rem;">{{ __('Clear') }}</a>
    @endif
</div>
</form>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Order #') }}</th>
                    <th>{{ __('Receiver') }}</th>
                    <th>{{ __('COD Amount') }}</th>
                    <th>{{ __('Del. Fee') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $payment = $order->payment;
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
                <tr>
                    <td><span style="font-family:monospace;font-size:.82rem;color:var(--red-lt);">{{ $order->order_number }}</span>
                        @if($order->batch_number)
                            <div class="cell-sub">{{ $order->batch_number }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="cell-main">{{ optional($receiver)->receiver_name }}</div>
                        <div class="cell-sub">{{ optional($receiver)->receiver_phone }}</div>
                    </td>
                    <td>
                        @if(optional($payment)->payment_type === 'cod' && optional($payment)->order_amount)
                            <span style="font-weight:700;color:#fbbf24;">{{ number_format($payment->order_amount, 2) }} JD</span>
                        @else
                            <span style="color:var(--text-dim);">—</span>
                        @endif
                    </td>
                    <td>
                        @if(optional($payment)->delivery_on_customer && optional($payment)->customer_delivery_amount)
                            <span style="font-weight:600;color:#60a5fa;">{{ number_format($payment->customer_delivery_amount, 2) }} JD</span>
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
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--text-dim);">
                        {{ __('No orders found.') }}
                        <a href="{{ route('client.orders.create') }}" style="color:var(--red-lt);text-decoration:none;"> {{ __('Create your first order →') }}</a>
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
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px;">{{ __('Delete Order?') }}</h3>
        <p style="font-size:.86rem;color:var(--text-sub);" id="deleteModalMsg">{{ __('This action cannot be undone.') }}</p>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button class="btn-secondary" style="flex:1;" onclick="closeDeleteModal()">{{ __('Cancel') }}</button>
            <form id="deleteForm" method="POST" style="flex:1;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger" style="width:100%;">{{ __('Delete') }}</button>
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
