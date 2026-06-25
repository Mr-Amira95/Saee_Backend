@extends('client.layouts.app')
@section('title', __('Track Orders'))
@section('page-title', __('Track Orders'))

@section('content')

<div style="background:var(--card);border:1px solid var(--bdr);border-radius:16px;padding:24px 26px;margin-bottom:24px;">
    <form method="GET" action="{{ route('client.track') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div class="filter-search-wrap" style="flex:1;min-width:260px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="q" type="text" class="filter-input" placeholder="{{ __('Order reference, receiver name, or phone…') }}" value="{{ $query }}" autofocus>
        </div>
        <button type="submit" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            {{ __('Search') }}
        </button>
        <a href="{{ route('client.dashboard') }}" class="btn-secondary">{{ __('← Back') }}</a>
    </form>
</div>

@if($query && $orders->isEmpty())
    <div style="text-align:center;padding:48px;color:var(--text-dim);">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24" style="margin-bottom:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <div style="font-size:.93rem;font-weight:600;color:var(--text-sub);">{{ __('No orders found') }}</div>
        <div style="font-size:.82rem;margin-top:5px;">No results for "{{ $query }}"</div>
    </div>
@elseif($orders->isNotEmpty())
    <div class="page-hd" style="margin-bottom:16px;">
        <div class="page-hd-left">
            <h1>{{ $orders->count() }} result{{ $orders->count() !== 1 ? 's' : '' }}</h1>
            <p>Showing orders matching "{{ $query }}"</p>
        </div>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Order #') }}</th>
                        <th>{{ __('Receiver') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    @php
                        $statusClass = match($order->status) {
                            'pending'   => 'badge-pending',
                            'picked_up' => 'badge-info',
                            'delivered' => 'badge-success',
                            'rejected'  => 'badge-danger',
                            default     => 'badge-neutral',
                        };
                        $statusLabel = match($order->status) {
                            'pending'   => __('Pending'),
                            'picked_up' => __('In Transit'),
                            'delivered' => __('Delivered'),
                            'rejected'  => __('Rejected'),
                            'returned'  => __('Returned'),
                            'cancelled' => __('Cancelled'),
                            default     => ucfirst($order->status),
                        };
                    @endphp
                    <tr>
                        <td><span style="font-family:monospace;font-size:.82rem;color:var(--red-lt);">{{ $order->order_number }}</span></td>
                        <td>
                            <div class="cell-main">{{ $order->receiver_name }}</div>
                            <div class="cell-sub">{{ $order->receiver_phone }}</div>
                        </td>
                        <td>
                            <div class="cell-main">{{ optional($order->city)->name }}</div>
                            <div class="cell-sub">{{ optional($order->area)->name }}</div>
                        </td>
                        <td><span class="badge {{ $order->payment_type === 'cod' ? 'badge-cod' : 'badge-prepaid' }}">{{ strtoupper($order->payment_type) }}</span></td>
                        <td><span class="badge {{ $statusClass }}"><span class="badge-dot"></span>{{ $statusLabel }}</span></td>
                        <td><span style="font-size:.8rem;color:var(--text-dim);">{{ $order->created_at->format('d M Y') }}</span></td>
                        <td>
                            <a href="{{ route('client.orders.show', $order) }}" class="act-btn act-btn-view" title="{{ __('View Details') }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
