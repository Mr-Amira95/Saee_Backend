@extends('admin.layouts.app')

@section('title', 'Orders Management')
@section('page-title', 'Orders Management')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Orders</span>
@endsection

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
                <div class="ms-lbl">All Pending Orders</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1m-6 0a1 1 0 001-1m-6 0H3"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['picked_up'] }}</div>
                <div class="ms-lbl">All Picked Up Orders</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['rejected'] }}</div>
                <div class="ms-lbl">All Rejected Orders</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['returned_today'] }}</div>
                <div class="ms-lbl">Returned Orders Today</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-4-6h8"/></svg>
            </div>
            <div>
                <div class="ms-val">{{ $stats['with_driver'] }}</div>
                <div class="ms-lbl">All Cash with Drivers</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-bar">
        <form action="{{ route('admin.orders.index') }}" method="GET" id="filter-form">

            <div class="filter-row">
                {{-- Search --}}
                <div class="filter-search-wrap" style="flex: 2; min-width: 0;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" id="live-search" value="{{ request('search') }}" class="filter-input" placeholder="Search order #, batch #, receiver name, phone...">
                </div>

                {{-- Status --}}
                <select name="status" class="filter-select" onchange="this.form.submit()" style="flex:1; min-width:0;">
                    <option value="">All Statuses</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                    <option value="returned"  {{ request('status') === 'returned'  ? 'selected' : '' }}>Returned</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                {{-- Searchable: Clients --}}
                <div class="ss-wrap" style="flex:1; min-width:0;">
                    <div class="ss-trigger" tabindex="0">
                        <span class="ss-label">
                            @php $selectedClient = $clients->firstWhere('id', request('client_profile_id')); @endphp
                            {{ $selectedClient ? $selectedClient->company_name : 'All Clients' }}
                        </span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="ss-panel">
                        <input type="text" class="ss-search-input" placeholder="Search clients...">
                        <div class="ss-opts">
                            <div class="ss-opt {{ !request('client_profile_id') ? 'selected' : '' }}" data-value="">All Clients</div>
                            @foreach($clients as $c)
                                <div class="ss-opt {{ request('client_profile_id') == $c->id ? 'selected' : '' }}" data-value="{{ $c->id }}">{{ $c->company_name }}</div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="client_profile_id" value="{{ request('client_profile_id') }}">
                </div>

                {{-- Searchable: Drivers --}}
                <div class="ss-wrap" style="flex:1; min-width:0;">
                    <div class="ss-trigger" tabindex="0">
                        <span class="ss-label">
                            @php $selectedDriver = $drivers->firstWhere('id', request('driver_id')); @endphp
                            {{ $selectedDriver ? $selectedDriver->name : 'All Drivers' }}
                        </span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="ss-panel">
                        <input type="text" class="ss-search-input" placeholder="Search drivers...">
                        <div class="ss-opts">
                            <div class="ss-opt {{ !request('driver_id') ? 'selected' : '' }}" data-value="">All Drivers</div>
                            @foreach($drivers as $d)
                                <div class="ss-opt {{ request('driver_id') == $d->id ? 'selected' : '' }}" data-value="{{ $d->id }}">{{ $d->name }}</div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="driver_id" value="{{ request('driver_id') }}">
                </div>

                {{-- Searchable: Cities --}}
                <div class="ss-wrap" style="flex:1; min-width:0;">
                    <div class="ss-trigger" tabindex="0">
                        <span class="ss-label">
                            @php $selectedCity = $cities->firstWhere('id', request('city_id')); @endphp
                            {{ $selectedCity ? $selectedCity->name : 'All Cities' }}
                        </span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="ss-panel">
                        <input type="text" class="ss-search-input" placeholder="Search cities...">
                        <div class="ss-opts">
                            <div class="ss-opt {{ !request('city_id') ? 'selected' : '' }}" data-value="">All Cities</div>
                            @foreach($cities as $city)
                                <div class="ss-opt {{ request('city_id') == $city->id ? 'selected' : '' }}" data-value="{{ $city->id }}">{{ $city->name }}</div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="city_id" value="{{ request('city_id') }}">
                </div>

                @if(request()->anyFilled(['search', 'status', 'payment_status', 'client_profile_id', 'driver_id', 'city_id']))
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="padding: 8px 14px; height: 35px; display: inline-flex; align-items: center; white-space: nowrap; flex-shrink: 0;">Clear</a>
                @endif
            </div>

        </form>
    </div>

    {{-- Assign Driver Modal --}}
    <div class="modal-overlay" id="assignDriverModal">
        <div class="modal-card" style="border-color: rgba(59,130,246,0.3); max-width: 440px;">
            <h3>Assign Driver to Order</h3>
            <p id="assign-modal-subtitle" style="margin-bottom: 18px;">Selected orders will be assigned and marked as Picked Up.</p>

            <form id="assign-form" method="POST" action="{{ route('admin.orders.assign-driver') }}">
                @csrf
                <div id="assign-order-ids"></div>

                <div class="form-group" style="text-align: left; margin-bottom: 22px;">
                    <label class="form-label" for="bulk_driver_id">Driver Name</label>
                    <select name="driver_id" id="bulk_driver_id" class="form-select" required style="width:100%">
                        <option value="">Select Driver</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('assignDriverModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="table-card">

        {{-- Bulk Action Bar --}}
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
                            <label class="custom-cb" title="Select all pending orders">
                                <input type="checkbox" id="select-all">
                                <span class="custom-cb-ring"></span>
                            </label>
                        </th>
                        <th>Order #</th>
                        <th>Batch #</th>
                        <th>Client</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th style="width: 100px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php $isPending = $order->status === 'pending'; @endphp
                        <tr class="{{ $isPending ? 'order-row-pending' : '' }}">
                            <td style="text-align:center;">
                                @if($isPending)
                                    <label class="custom-cb">
                                        <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
                                        <span class="custom-cb-ring"></span>
                                    </label>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                @if($order->batch_number)
                                    <a href="{{ route('admin.orders.index', ['search' => $order->batch_number]) }}" style="color: var(--text-sub); font-size: 0.76rem; font-family: monospace; text-decoration: none;" title="Filter by this batch">
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
                                @if($order->driver)
                                    <div class="cell-main">{{ $order->driver->name }}</div>
                                @else
                                    <span class="cell-sub" style="font-style: italic;">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending'   => 'badge-pending',
                                        'picked_up' => 'badge-info',
                                        'delivered' => 'badge-active',
                                        'rejected'  => 'badge-suspended',
                                        'returned'  => 'badge-no',
                                        'cancelled' => 'badge-suspended',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$order->status] ?? 'badge-no' }}">
                                    <span class="badge-dot"></span>
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
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
                            <td colspan="7" style="text-align: center; color: var(--text-dim); padding: 30px;">
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

    <style>
    /* Filter row layout */
    .filter-bar form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
    }

    /* Searchable select — matches dark admin theme */
    .ss-wrap {
        position: relative;
        min-width: 0;
    }
    .ss-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 12px;
        height: 35px;
        border: 1px solid var(--bdr);
        border-radius: 8px;
        background: var(--card);
        cursor: pointer;
        font-size: .875rem;
        color: var(--text);
        gap: 8px;
        user-select: none;
        transition: border-color .15s;
    }
    .ss-trigger:hover,
    .ss-wrap.open .ss-trigger {
        border-color: var(--red-lt);
    }
    .ss-label {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ss-panel {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,.35);
        z-index: 200;
        display: none;
        overflow: hidden;
    }
    .ss-wrap.open .ss-panel {
        display: block;
    }
    .ss-search-input {
        width: 100%;
        padding: 8px 12px;
        border: none;
        border-bottom: 1px solid var(--bdr);
        font-size: .875rem;
        outline: none;
        background: var(--card);
        color: var(--text);
        box-sizing: border-box;
    }
    .ss-opts {
        max-height: 200px;
        overflow-y: auto;
    }
    .ss-opt {
        padding: 9px 14px;
        font-size: .875rem;
        cursor: pointer;
        color: var(--text);
        transition: background .1s;
    }
    .ss-opt:hover {
        background: rgba(255,255,255,.08);
    }
    .ss-opt.selected {
        color: var(--red-lt);
        font-weight: 600;
    }
    .ss-opt.ss-hidden {
        display: none;
    }

    /* Custom checkboxes */
    .custom-cb {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        width: 18px;
        height: 18px;
    }
    .custom-cb input[type="checkbox"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        cursor: pointer;
        z-index: 1;
    }
    .custom-cb-ring {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid var(--border, #d1d5db);
        background: var(--card-bg, #fff);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color .15s, background .15s, box-shadow .15s;
        flex-shrink: 0;
    }
    .custom-cb:hover .custom-cb-ring {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    }
    .custom-cb input:checked ~ .custom-cb-ring {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .custom-cb input:checked ~ .custom-cb-ring::after {
        content: '';
        display: block;
        width: 5px;
        height: 9px;
        border: 2px solid #fff;
        border-top: none;
        border-left: none;
        transform: rotate(45deg) translate(-1px, -1px);
    }
    .custom-cb input:indeterminate ~ .custom-cb-ring {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .custom-cb input:indeterminate ~ .custom-cb-ring::after {
        content: '';
        display: block;
        width: 8px;
        height: 2px;
        background: #fff;
        border-radius: 2px;
    }
    </style>

    <script>
    // Modal helpers
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    // Live search — debounce 350ms then submit
    (function() {
        var input = document.getElementById('live-search');
        var timer;
        if (!input) return;
        input.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(function() {
                document.getElementById('filter-form').submit();
            }, 350);
        });
    })();

    // Searchable selects
    document.querySelectorAll('.ss-wrap').forEach(function(wrap) {
        var trigger    = wrap.querySelector('.ss-trigger');
        var label      = wrap.querySelector('.ss-label');
        var searchInp  = wrap.querySelector('.ss-search-input');
        var opts       = wrap.querySelectorAll('.ss-opt');
        var hidden     = wrap.querySelector('input[type=hidden]');
        var form       = document.getElementById('filter-form');

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = wrap.classList.contains('open');
            // close all
            document.querySelectorAll('.ss-wrap.open').forEach(function(w) { w.classList.remove('open'); });
            if (!isOpen) {
                wrap.classList.add('open');
                searchInp.value = '';
                opts.forEach(function(o) { o.classList.remove('ss-hidden'); });
                searchInp.focus();
            }
        });

        searchInp.addEventListener('input', function() {
            var val = this.value.toLowerCase();
            opts.forEach(function(opt) {
                opt.classList.toggle('ss-hidden', val !== '' && !opt.textContent.toLowerCase().includes(val));
            });
        });

        opts.forEach(function(opt) {
            opt.addEventListener('click', function() {
                hidden.value = this.dataset.value;
                label.textContent = this.textContent.trim();
                opts.forEach(function(o) { o.classList.remove('selected'); });
                this.classList.add('selected');
                wrap.classList.remove('open');
                form.submit();
            });
        });

        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) wrap.classList.remove('open');
        });
    });

    // Bulk select
    (function() {
        var selectAll   = document.getElementById('select-all');
        var bulkBar     = document.getElementById('bulk-bar');
        var bulkCount   = document.getElementById('bulk-count');
        var orderIdsDiv = document.getElementById('assign-order-ids');
        var subtitle    = document.getElementById('assign-modal-subtitle');

        function getChecked() {
            return Array.from(document.querySelectorAll('.order-checkbox:checked'));
        }

        function updateBulkBar() {
            var checked = getChecked();
            var total   = document.querySelectorAll('.order-checkbox').length;
            bulkBar.style.display = checked.length > 0 ? 'flex' : 'none';
            bulkCount.textContent = checked.length + ' order' + (checked.length > 1 ? 's' : '') + ' selected';
            selectAll.indeterminate = checked.length > 0 && checked.length < total;
            selectAll.checked = total > 0 && checked.length === total;
        }

        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.order-checkbox').forEach(function(cb) { cb.checked = selectAll.checked; });
            updateBulkBar();
        });

        document.querySelectorAll('.order-checkbox').forEach(function(cb) {
            cb.addEventListener('change', updateBulkBar);
        });

        window.clearSelection = function() {
            document.querySelectorAll('.order-checkbox').forEach(function(cb) { cb.checked = false; });
            selectAll.checked = false;
            bulkBar.style.display = 'none';
        };

        function buildHiddenInputs(ids) {
            orderIdsDiv.innerHTML = '';
            ids.forEach(function(id) {
                var input  = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'order_ids[]';
                input.value = id;
                orderIdsDiv.appendChild(input);
            });
        }

        window.openBulkAssign = function() {
            var ids = getChecked().map(function(cb) { return cb.value; });
            buildHiddenInputs(ids);
            subtitle.textContent = ids.length + ' pending order' + (ids.length > 1 ? 's' : '') + ' will be assigned and marked as Picked Up.';
            openModal('assignDriverModal');
        };

        window.openSingleAssign = function(orderId, orderLabel) {
            buildHiddenInputs([orderId]);
            subtitle.textContent = 'Order ' + orderLabel + ' will be assigned and marked as Picked Up.';
            openModal('assignDriverModal');
        };
    })();
    </script>
@endsection
