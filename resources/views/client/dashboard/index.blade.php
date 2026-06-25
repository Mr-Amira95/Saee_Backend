@extends('client.layouts.app')
@section('title', __('Home'))
@section('page-title', __('Home'))

@section('content')

{{-- Tracking search --}}
<div style="background:var(--card);border:1px solid var(--bdr);border-radius:16px;padding:24px 26px;margin-bottom:24px;animation:fu .4s both;">
    <div style="font-size:.8rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px;">{{ __('Track a Shipment') }}</div>
    <form method="GET" action="{{ route('client.track') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div class="filter-search-wrap" style="flex:1;min-width:260px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="q" type="text" class="filter-input" placeholder="{{ __('Order reference, receiver name, or phone…') }}" value="{{ request('q') }}" autofocus>
        </div>
        <button type="submit" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            {{ __('Search') }}
        </button>
    </form>
</div>

{{-- Active orders --}}
<div class="page-hd" style="margin-bottom:16px;">
    <div class="page-hd-left">
        <h1>{{ __('Active Orders') }}</h1>
        <p>{{ __('Your pending and in-transit shipments') }}</p>
    </div>
    <div class="page-hd-right">
        <a href="{{ route('client.orders.index') }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            {{ __('All Orders') }}
        </a>
        <a href="{{ route('client.orders.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ __('New Order') }}
        </a>
    </div>
</div>

@if($activeOrders->isEmpty())
    <div style="background:var(--card);border:1px solid var(--bdr);border-radius:14px;padding:48px;text-align:center;animation:fu .45s .1s both;">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--text-dim);margin-bottom:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <div style="font-size:1rem;font-weight:600;color:var(--text-sub);">{{ __('No active orders') }}</div>
        <div style="font-size:.84rem;color:var(--text-dim);margin-top:6px;">{{ __('Create your first order to get started') }}</div>
        <a href="{{ route('client.orders.create') }}" class="btn-primary" style="margin-top:18px;display:inline-flex;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ __('Create Order') }}
        </a>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;animation:fu .45s .1s both;">
        @foreach($activeOrders as $order)
        @php
            $statusMap = [
                'pending'   => ['label' => __('Pending'),    'class' => 'badge-pending'],
                'picked_up' => ['label' => __('In Transit'), 'class' => 'badge-info'],
            ];
            $st = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'badge-neutral'];
        @endphp
        <a href="{{ route('client.orders.show', $order) }}" style="text-decoration:none;">
            <div style="background:var(--card);border:1px solid var(--bdr);border-radius:14px;padding:18px 20px;transition:border-color .15s,transform .15s;cursor:pointer;" onmouseenter="this.style.borderColor='rgba(220,38,38,.3)';this.style.transform='translateY(-2px)';" onmouseleave="this.style.borderColor='rgba(255,255,255,.06)';this.style.transform='translateY(0)';">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <span style="font-size:.78rem;font-weight:700;color:var(--text-dim);font-family:monospace;">{{ $order->order_number }}</span>
                    <span class="badge {{ $st['class'] }}"><span class="badge-dot"></span>{{ $st['label'] }}</span>
                </div>
                <div style="font-size:.93rem;font-weight:600;color:var(--text);margin-bottom:4px;">{{ $order->receiver_name }}</div>
                <div style="font-size:.8rem;color:var(--text-dim);margin-bottom:10px;">
                    {{ optional($order->city)->name }}{{ $order->area ? ', ' . $order->area->name : '' }}
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid rgba(255,255,255,.04);">
                    <div>
                        @if($order->payment_type === 'cod')
                            <span style="font-size:.8rem;color:var(--text-dim);">{{ __('COD') }}</span>
                            <span style="font-size:.95rem;font-weight:700;color:#fbbf24;margin-left:5px;">{{ number_format($order->order_price, 2) }} JD</span>
                        @else
                            <span class="badge badge-prepaid">{{ __('Prepaid') }}</span>
                        @endif
                    </div>
                    <span style="font-size:.74rem;color:var(--text-dim);">{{ $order->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
@endif

@endsection
