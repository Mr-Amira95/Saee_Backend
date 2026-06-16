@extends('admin.layouts.app')

@section('title', 'Orders Management')
@section('page-title', 'Orders Management')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Orders</span>
@@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Orders Management</h1>
            <p>Create, track, and manage all courier shipments.</p>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.orders.import') }}" class="btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Bulk Import CSV
            </a>
            <a href="{{ route('admin.orders.create') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Order
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="mini-stats">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['pending'] }}</div>
                <div class="ms-lbl">Pending Collection</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1m-6 0a1 1 0 001-1m-6 0H3"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['picked_up'] }}</div>
                <div class="ms-lbl">In Transit</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['delivered'] }}</div>
                <div class="ms-lbl">Delivered</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-4-6h8"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['with_driver'] }}</div>
                <div class="ms-lbl">Cash with Drivers</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-bar">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="filter-form">
            <div class="filter-search-wrap">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Search Order #, customer name, phone...">
            </div>

            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <select name="payment_status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Payments</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending Collection</option>
                <option value="with_driver" {{ request('payment_status') === 'with_driver' ? 'selected' : '' }}>With Driver</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="no_payment" {{ request('payment_status') === 'no_payment' ? 'selected' : '' }}>No Payment</option>
            </select>

            <select name="client_profile_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_profile_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>

            <select name="driver_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Drivers</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ request('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>

            <select name="city_id" class="filter-select" onchange="this.form.submit()">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                @endforeach
            </select>

            @if(request()->anyFilled(['search', 'status', 'payment_status', 'client_profile_id', 'driver_id', 'city_id']))
                <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="padding: 8px 12px; height: 35px; display: inline-flex; align-items: center;">Clear</a>
            @endif
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Client</th>
                        <th>Receiver</th>
                        <th>Pricing / Payment</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Driver</th>
                        <th>Created At</th>
                        <th style="width: 80px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->clientProfile->company_name }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->receiver_name }}</div>
                                <div class="cell-sub">{{ $order->receiver_phone }} • {{ $order->city->name }}</div>
                            </td>
                            <td>
                                <div class="cell-main">
                                    @if($order->payment_type === 'cod')
                                        COD: <strong>{{ number_format($order->order_price, 2) }} JD</strong>
                                    @else
                                        <span class="badge badge-no" style="font-size: .65rem; padding: 2px 6px;">Prepaid</span>
                                    @endif
                                </div>
                                <div class="cell-sub">Shipping: {{ number_format($order->delivery_amount, 2) }} JD</div>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'badge-pending',
                                        'picked_up' => 'badge-info',
                                        'delivered' => 'badge-active',
                                        'rejected' => 'badge-suspended',
                                        'returned' => 'badge-no',
                                        'cancelled' => 'badge-suspended',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$order->status] ?? 'badge-no' }}">
                                    <span class="badge-dot"></span>
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $payClasses = [
                                        'pending' => 'badge-pending',
                                        'with_driver' => 'badge-info',
                                        'paid' => 'badge-active',
                                        'no_payment' => 'badge-no',
                                    ];
                                @endphp
                                <span class="badge {{ $payClasses[$order->payment_status] ?? 'badge-no' }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                            </td>
                            <td>
                                @if($order->driver)
                                    <div class="cell-main">{{ $order->driver->name }}</div>
                                @else
                                    <span class="cell-sub" style="font-style: italic;">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <div class="cell-sub">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                            </td>
                            <td>
                                <div class="act-btns" style="justify-content: center;">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="act-btn act-view" title="View Details">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button class="act-btn act-delete" onclick="confirmDelete('{{ route('admin.orders.destroy', $order) }}', 'Order #{{ $order->order_number }}')" title="Delete">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                No orders found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries
                </div>
                <div class="pag-links">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
