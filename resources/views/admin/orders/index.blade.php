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

            <input type="text" name="batch_number" value="{{ request('batch_number') }}" class="filter-input" placeholder="Batch #..." style="width: 180px; font-family: monospace;" oninput="if(!this.value) this.form.submit()">

            @if(request()->anyFilled(['search', 'status', 'payment_status', 'client_profile_id', 'driver_id', 'city_id', 'batch_number']))
                <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="padding: 8px 12px; height: 35px; display: inline-flex; align-items: center;">Clear</a>
            @endif
        </form>
    </div>

    {{-- Assign Driver Modal --}}
    <div id="assign-modal" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
        <div style="background:var(--card-bg,#fff); border-radius:10px; padding:28px 32px; min-width:360px; max-width:480px; box-shadow:0 8px 32px rgba(0,0,0,.18);">
            <h3 style="margin:0 0 6px; font-size:1.05rem;">Assign Driver</h3>
            <p id="assign-modal-subtitle" style="margin:0 0 20px; color:var(--text-sub,#666); font-size:.85rem;"></p>

            <form id="assign-form" method="POST" action="{{ route('admin.orders.assign-driver') }}">
                @csrf
                <div id="assign-order-ids"></div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:.82rem; font-weight:600; margin-bottom:6px; color:var(--text-sub,#555);">Select Driver</label>
                    <select name="driver_id" required style="width:100%; padding:9px 12px; border:1px solid var(--border,#ddd); border-radius:7px; font-size:.9rem; background:var(--input-bg,#fff); color:var(--text,#222);">
                        <option value="">— Choose a driver —</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" onclick="closeAssignModal()" style="padding:9px 20px; border:1px solid var(--border,#ddd); border-radius:7px; background:transparent; cursor:pointer; font-size:.88rem;">Cancel</button>
                    <button type="submit" class="btn-primary" style="padding:9px 22px; font-size:.88rem;">
                        Assign & Mark Picked Up
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="table-card">

        {{-- Bulk Action Bar (hidden until rows are selected) --}}
        <div id="bulk-bar" style="display:none; align-items:center; gap:12px; padding:12px 20px; background:rgba(59,130,246,.07); border-bottom:1px solid rgba(59,130,246,.15);">
            <svg width="16" height="16" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="bulk-count" style="font-size:.88rem; color:#3b82f6; font-weight:600;"></span>
            <button onclick="openBulkAssign()" class="btn-primary" style="padding:7px 16px; font-size:.83rem; margin-left:4px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="margin-right:5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Assign to Driver
            </button>
            <button onclick="clearSelection()" style="padding:7px 14px; font-size:.83rem; border:1px solid var(--border,#ddd); border-radius:7px; background:transparent; cursor:pointer; color:var(--text-sub,#555);">Clear</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">
                            <input type="checkbox" id="select-all" title="Select all pending orders" style="cursor:pointer; width:15px; height:15px;">
                        </th>
                        <th>Order #</th>
                        <th>Batch #</th>
                        <th>Client</th>
                        <th>Receiver</th>
                        <th>Pricing / Payment</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Driver</th>
                        <th>Created At</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php $isPending = $order->status === 'pending'; @endphp
                        <tr class="{{ $isPending ? 'order-row-pending' : '' }}">
                            <td style="text-align:center;">
                                @if($isPending)
                                    <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" style="cursor:pointer; width:15px; height:15px;">
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                @if($order->batch_number)
                                    <a href="{{ route('admin.orders.index', ['batch_number' => $order->batch_number]) }}" style="color: var(--text-sub); font-size: 0.76rem; font-family: monospace; text-decoration: none;" title="Filter by this batch">
                                        {{ $order->batch_number }}
                                    </a>
                                @else
                                    <span class="cell-sub" style="font-style: italic;">—</span>
                                @endif
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
                                    @if($isPending)
                                        <button class="act-btn act-edit" title="Assign Driver" onclick="openSingleAssign({{ $order->id }}, '#{{ $order->order_number }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </button>
                                        <button class="act-btn act-delete" onclick="confirmDelete('{{ route('admin.orders.destroy', $order) }}', 'Order #{{ $order->order_number }}')" title="Delete">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; color: var(--text-dim); padding: 30px;">
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

    <script>
    (function () {
        const selectAll   = document.getElementById('select-all');
        const bulkBar     = document.getElementById('bulk-bar');
        const bulkCount   = document.getElementById('bulk-count');
        const modal       = document.getElementById('assign-modal');
        const orderIdsDiv = document.getElementById('assign-order-ids');
        const subtitle    = document.getElementById('assign-modal-subtitle');

        function getChecked() {
            return Array.from(document.querySelectorAll('.order-checkbox:checked'));
        }

        function updateBulkBar() {
            const checked = getChecked();
            if (checked.length > 0) {
                bulkCount.textContent = checked.length + ' order' + (checked.length > 1 ? 's' : '') + ' selected';
                bulkBar.style.display = 'flex';
            } else {
                bulkBar.style.display = 'none';
            }
            selectAll.indeterminate = checked.length > 0 && checked.length < document.querySelectorAll('.order-checkbox').length;
            selectAll.checked = checked.length > 0 && checked.length === document.querySelectorAll('.order-checkbox').length;
        }

        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });

        document.querySelectorAll('.order-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });

        window.clearSelection = function () {
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
            selectAll.checked = false;
            bulkBar.style.display = 'none';
        };

        function buildHiddenInputs(ids) {
            orderIdsDiv.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'order_ids[]';
                input.value = id;
                orderIdsDiv.appendChild(input);
            });
        }

        window.openBulkAssign = function () {
            const ids = getChecked().map(cb => cb.value);
            buildHiddenInputs(ids);
            subtitle.textContent = ids.length + ' pending order' + (ids.length > 1 ? 's' : '') + ' will be assigned and marked as Picked Up.';
            modal.style.display = 'flex';
        };

        window.openSingleAssign = function (orderId, orderLabel) {
            buildHiddenInputs([orderId]);
            subtitle.textContent = 'Order ' + orderLabel + ' will be assigned and marked as Picked Up.';
            modal.style.display = 'flex';
        };

        window.closeAssignModal = function () {
            modal.style.display = 'none';
            orderIdsDiv.innerHTML = '';
        };

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeAssignModal();
        });
    })();
    </script>
@endsection
