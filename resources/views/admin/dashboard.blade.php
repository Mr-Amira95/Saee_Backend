@extends('admin.layouts.app')

@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))

@section('breadcrumb')
    <span>/</span>
    <span>{{ __('Overview') }}</span>
@endsection

@section('head')
<style>
    /* ─── Welcome ─────────────────────────────────── */
    .welcome-row {
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, rgba(127,29,29,.35) 0%, rgba(12,18,48,.9) 60%);
        border: 1px solid rgba(220,38,38,.15); border-radius: 16px;
        padding: 24px 28px; margin-bottom: 20px; gap: 16px; flex-wrap: wrap;
        animation: fu .45s .05s both;
    }
    .welcome-text h2 { font-size: 1.3rem; font-weight: 800; letter-spacing: -.02em; }
    .welcome-text h2 em { color: var(--red-lt); font-style: normal; }
    .welcome-text p  { font-size: .82rem; color: var(--text-sub); margin-top: 5px; }
    .welcome-date    { font-size: .8rem; color: var(--text-dim); font-weight: 500; white-space: nowrap; }

    /* Light Theme welcome row styling overrides */
    html.light-theme .welcome-row {
        background: linear-gradient(135deg, rgba(220,38,38,0.05) 0%, rgba(255,255,255,0.9) 100%);
        border-color: rgba(220,38,38,0.12);
    }
    html.light-theme .welcome-text h2 em {
        color: var(--red);
    }
    html.light-theme .attendance-widget {
        background: rgba(15, 23, 42, 0.02) !important;
        border-color: rgba(15, 23, 42, 0.08) !important;
    }

    /* ─── Operational status cards (clickable when count > 0) ─── */
    .status-card {
        display: block; text-decoration: none;
        background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px;
        padding: 12px; text-align: center;
        transition: transform .13s, border-color .13s, background .13s;
    }
    .status-card-clickable { cursor: pointer; }
    .status-card-clickable:hover { transform: translateY(-2px); border-color: rgba(255,255,255,.16); }
    html.light-theme .status-card-clickable:hover { border-color: rgba(15,23,42,.18); }
    .status-card-active { background: rgba(220,38,38,.09); border-color: rgba(220,38,38,.35); }

    /* ─── Metric cards ────────────────────────────── */
    .cards-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px; margin-bottom: 20px;
    }
    .metric {
        background: var(--card); border: 1px solid var(--bdr); border-radius: 14px;
        padding: 20px; backdrop-filter: blur(8px); animation: fu .45s both;
        position: relative; overflow: hidden; transition: border-color .2s;
    }
    .metric::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 100% 0%, var(--m-icon-bg, rgba(220,38,38,.1)) 0%, transparent 60%);
        pointer-events: none;
    }
    html[dir="rtl"] .metric::before {
        background: radial-gradient(circle at 0% 0%, var(--m-icon-bg, rgba(220,38,38,.1)) 0%, transparent 60%);
    }
    .metric:hover { border-color: rgba(var(--m-color, 220,38,38),.18); }
    .metric-head  { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .metric-icon  {
        width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        background: var(--m-icon-bg, rgba(220,38,38,.1)); color: var(--m-color, #ef4444);
    }
    .metric-trend { font-size: .7rem; font-weight: 700; color: #4ade80; }
    .metric-val   { font-size: 1.65rem; font-weight: 900; letter-spacing: -.03em; line-height: 1; margin-bottom: 5px; }
    .metric-lbl   { font-size: .75rem; color: var(--text-dim); font-weight: 500; }

    /* ─── Bottom two-col ─────────────────────────── */
    .bottom-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }

    .panel {
        background: var(--card); border: 1px solid var(--bdr); border-radius: 14px;
        overflow: hidden; backdrop-filter: blur(8px); animation: fu .5s .35s both;
    }
    .panel-head  {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid var(--bdr);
    }
    .panel-title { font-size: .82rem; font-weight: 700; color: var(--text-sub); letter-spacing: .04em; text-transform: uppercase; }
    .panel-link  { font-size: .75rem; color: var(--red-lt); text-decoration: none; }
    .panel-link:hover { text-decoration: underline; }

    /* Quick stats */
    .quick-stats  { padding: 8px 0; }
    .qs-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 11px 20px; gap: 12px; transition: background .12s;
    }
    .qs-item:hover { background: rgba(255,255,255,.018); }
    .qs-left  { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
    .qs-icon  { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .qs-label { font-size: .8rem; color: var(--text-sub); font-weight: 500; }
    .qs-bar-wrap { height: 3px; background: rgba(255,255,255,.06); border-radius: 2px; margin-top: 4px; width: 100px; }
    .qs-bar   { height: 3px; border-radius: 2px; width: 0; transition: width 1s cubic-bezier(.4,0,.2,1) .4s; }
    .qs-val   { font-size: .76rem; font-weight: 700; color: var(--text-dim); white-space: nowrap; }

    /* ─── Live map shortcut ───────────────────────── */
    .live-map-panel { margin-bottom: 20px; animation: fu .5s .08s both; }
    .live-map-meta  { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .live-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 9px; border-radius: 100px;
        background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2);
        font-size: .68rem; font-weight: 600; color: #4ade80;
    }
    .live-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #22c55e; box-shadow: 0 0 5px #22c55e;
        animation: dot-p 1.6s infinite;
    }
    @keyframes dot-p { 0%,100%{opacity:1;} 50%{opacity:.3;} }
    #dashboardMap { width: 100%; height: 300px; background: #0c1230; z-index: 0; }
    html.light-theme #dashboardMap { background: #e5e3df; }
    .gmap-driver-marker {
        position: absolute; transform: translate(-50%, -50%); cursor: pointer;
        width: 32px; height: 32px; border-radius: 50%;
        background: linear-gradient(135deg,#7f1d1d,#dc2626); border: 2px solid #f87171;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700; color: #fff; font-family: inherit;
        box-shadow: 0 2px 8px rgba(0,0,0,.5);
    }
    .gmap-popup { font-size: .8rem; line-height: 1.7; color: #0f172a; }
    .gmap-popup b { color: #dc2626; }
    .gmap-popup .ts { color: #64748b; font-size: .75em; }
    html:not(.light-theme) .gm-style .gm-style-iw-c { background: #0c1230; border: 1px solid rgba(255,255,255,.1); box-shadow: 0 8px 30px rgba(0,0,0,.6); }
    html:not(.light-theme) .gm-style .gm-style-iw-d { overflow: auto !important; }
    html:not(.light-theme) .gm-style .gm-style-iw-tc::after { background: #0c1230; }
    html:not(.light-theme) .gm-style .gm-ui-hover-effect > span { background-color: #94a3b8 !important; }
    html:not(.light-theme) .gmap-popup { color: #f1f5f9; }
    html:not(.light-theme) .gmap-popup b { color: #fca5a5; }
    .live-map-empty {
        position: absolute; inset: 0; z-index: 5; pointer-events: none;
        display: flex; align-items: center; justify-content: center;
    }
    .live-map-empty > div {
        background: var(--card); backdrop-filter: blur(6px); border: 1px solid var(--bdr);
        border-radius: 12px; padding: 14px 22px; font-size: .8rem; color: var(--text-sub); text-align: center;
    }

    /* ─── Mobile adjustments ──────────────────────── */
    @media (max-width: 600px) {
        .welcome-row { padding: 16px 18px; }
        /* attendance-widget uses inline min-width:280px which is too tight once welcome-row
           wraps on narrow screens; let it take the full row width instead. */
        .attendance-widget { min-width: 100% !important; }
        /* customToast is only right/left-anchored (not both) with max-width:380px, which can
           push it past the opposite viewport edge on phones; dock it to both sides instead. */
        #customToast { left: 12px !important; right: 12px !important; max-width: calc(100% - 24px) !important; }
    }
</style>
@endsection

@section('content')
{{-- Welcome & Shift Log row --}}
<div class="welcome-row">
    <div class="welcome-text" style="flex: 1; min-width: 200px;">
        <h2>{{ now()->format('G') < 12 ? __('Good morning') : (now()->format('G') < 17 ? __('Good afternoon') : __('Good evening')) }}, <em>{{ explode(' ', auth()->user()->name)[0] }}</em></h2>
        <p>{{ __("Here's what's happening at Sa'ee Logistics today.") }}</p>
    </div>

    {{-- Geolocation Shift Widget --}}
    @if(auth()->user()->isDriver() || auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
        @php
            $todayAttendance = \App\Models\Attendance::where('user_id', auth()->id())
                ->where('date', now()->toDateString())
                ->latest('check_in_at')
                ->first();
            $todaySessionCount = \App\Models\Attendance::where('user_id', auth()->id())
                ->where('date', now()->toDateString())
                ->count();
        @endphp
        <div class="attendance-widget" style="background: rgba(255,255,255,.03); border: 1px solid var(--bdr); border-radius: 12px; padding: 12px 18px; display: flex; align-items: center; gap: 15px; min-width: 280px; backdrop-filter: blur(8px);">
            <div style="flex: 1; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">
                <div style="font-size: .68rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em;">
                    {{ __('Shift Logs') }}
                    @if($todaySessionCount > 1)
                        <span style="color: var(--red-lt); margin-left: 4px;">({{ $todaySessionCount }} {{ __('sessions') }})</span>
                    @endif
                </div>
                <div id="attendanceStatus" style="font-size: .84rem; font-weight: 600; margin-top: 3px; color: var(--text);">
                    @if(!$todayAttendance)
                        {{ __('Not Checked In') }}
                    @elseif(!$todayAttendance->check_out_at)
                        {{ __('Working since') }} {{ $todayAttendance->check_in_at->format('H:i') }}
                    @else
                        {{ __('Last shift:') }} {{ $todayAttendance->check_in_at->format('H:i') }} – {{ $todayAttendance->check_out_at->format('H:i') }}
                    @endif
                </div>
                @if($todayAttendance && !$todayAttendance->check_out_at)
                    <div id="activeTimer" style="font-size: .74rem; color: var(--red-lt); font-family: monospace; font-weight: 600; margin-top: 2px;" data-start="{{ $todayAttendance->check_in_at->toIso8601String() }}">00:00:00</div>
                @endif
            </div>

            <div style="display: flex; gap: 8px;">
                @if(!$todayAttendance || $todayAttendance->check_out_at)
                    <button class="btn-primary" id="dashboardCheckInBtn" onclick="submitAttendance('check-in')" style="padding: 8px 14px; font-size: .8rem; box-shadow: none;">{{ __('Check In') }}</button>
                @else
                    <button class="btn-danger" id="dashboardCheckOutBtn" onclick="submitAttendance('check-out')" style="padding: 8px 14px; font-size: .8rem;">{{ __('Check Out') }}</button>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Live driver map shortcut --}}
@if($canViewLiveMap)
<div class="panel live-map-panel">
    <div class="panel-head">
        <div class="live-map-meta">
            <span class="panel-title">{{ __('Live Driver Map') }}</span>
            <span class="live-badge"><span class="live-dot"></span>{{ __('Live') }}</span>
            <span style="font-size:.72rem;color:var(--text-dim);" id="dashboardDriverCount">
                {{ $mapDrivers->count() }} {{ __('driver(s) with known location') }}
            </span>
        </div>
        <a href="{{ route('admin.drivers.live-map') }}" class="btn-primary" style="padding: 6px 14px; font-size: .75rem; box-shadow: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            {{ __('Open Live Map') }}
        </a>
    </div>
    <div style="position:relative;">
        <div id="dashboardMap"></div>
        @if(! config('services.google.maps_api_key'))
        <div class="live-map-empty">
            <div>{{ __('Google Maps API key is not configured.') }}</div>
        </div>
        @elseif($mapDrivers->isEmpty())
        <div class="live-map-empty" id="dashboardMapEmpty">
            <div>{{ __('No drivers with a known location yet.') }}</div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Metric cards --}}
<div class="cards-grid">
    <div class="metric" style="--m-color:#ef4444;--m-icon-bg:rgba(220,38,38,.12);animation-delay:.1s">
        <div class="metric-head">
            <div class="metric-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <span class="metric-trend">{{ __('↑ Active') }}</span>
        </div>
        <div class="metric-val">{{ $activeDriversCount }}</div>
        <div class="metric-lbl">{{ __('Active Drivers') }}</div>
    </div>

    <div class="metric" style="--m-color:#3b82f6;--m-icon-bg:rgba(59,130,246,.12);animation-delay:.15s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#60a5fa">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="metric-trend">{{ __('↑ Registered') }}</span>
        </div>
        <div class="metric-val">{{ $activeClientsCount }}</div>
        <div class="metric-lbl">{{ __('Client Companies') }}</div>
    </div>

    <div class="metric" style="--m-color:#a855f7;--m-icon-bg:rgba(168,85,247,.12);animation-delay:.2s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#c084fc">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="metric-trend">{{ __('Total') }}</span>
        </div>
        <div class="metric-val">{{ $totalOrdersCount }}</div>
        <div class="metric-lbl">{{ __('Total Orders') }}</div>
    </div>

    <div class="metric" style="--m-color:#10b981;--m-icon-bg:rgba(16,185,129,.12);animation-delay:.25s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#34d399">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="metric-trend">{{ __('Delivered') }}</span>
        </div>
        <div class="metric-val">{{ number_format($totalRevenue, 2) }}</div>
        <div class="metric-lbl">{{ __('Revenue (JD)') }}</div>
    </div>
</div>

{{-- Segmented Status Bar & Operational Distribution Card --}}
@php
    $totalCount = array_sum($statusCounts);
    $getPercentage = function($status) use ($statusCounts, $totalCount) {
        if ($totalCount === 0) return 0;
        return (($statusCounts[$status] ?? 0) / $totalCount) * 100;
    };
@endphp
<div class="panel" style="margin-bottom: 20px; animation: fu .5s .15s both;">
    <div class="panel-head">
        <span class="panel-title">{{ __('Operational Order Status') }}</span>
        <span style="font-size: .72rem; color: var(--text-dim); font-weight: 500;">{{ __('Live distribution across states') }}</span>
    </div>
    <div style="padding: 20px 24px;">
        <!-- Status Bar -->
        <div style="height: 10px; display: flex; border-radius: 5px; overflow: hidden; background: rgba(255,255,255,.04); margin-bottom: 20px;">
            <div style="width: {{ $getPercentage('pending') }}%; background: var(--warning);" title="{{ __('Pending') }}: {{ round($getPercentage('pending'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('picked_up') }}%; background: var(--info);" title="{{ __('In Transit') }}: {{ round($getPercentage('picked_up'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('delivered') }}%; background: var(--success);" title="{{ __('Delivered') }}: {{ round($getPercentage('delivered'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('rejected') + $getPercentage('returned') }}%; background: #f87171;" title="{{ __('Failed/Returned') }}: {{ round($getPercentage('rejected') + $getPercentage('returned'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('cancelled') }}%; background: var(--text-dim);" title="{{ __('Cancelled') }}: {{ round($getPercentage('cancelled'), 1) }}%"></div>
        </div>
        <!-- Grid details — click a non-zero card to list its orders below -->
        @php
            $statusCards = [
                'pending'         => ['label' => __('Pending'),         'badge' => 'badge-pending', 'count' => $statusCounts['pending'] ?? 0],
                'in_transit'      => ['label' => __('In Transit'),      'badge' => 'badge-info',    'count' => $statusCounts['picked_up'] ?? 0],
                'delivered'       => ['label' => __('Delivered'),       'badge' => 'badge-success', 'count' => $statusCounts['delivered'] ?? 0],
                'returned_failed' => ['label' => __('Returned/Failed'), 'badge' => 'badge-danger',  'count' => ($statusCounts['returned'] ?? 0) + ($statusCounts['rejected'] ?? 0)],
                'cancelled'       => ['label' => __('Cancelled'),       'badge' => 'badge-neutral', 'count' => $statusCounts['cancelled'] ?? 0],
            ];
        @endphp
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
            @foreach($statusCards as $key => $card)
                @php $cardActive = $selectedStatus === $key ? ' status-card-active' : ''; @endphp
                @if($card['count'] > 0)
                    <a href="{{ route('admin.dashboard', ['status' => $key]) }}#order-status" class="status-card status-card-clickable{{ $cardActive }}">
                @else
                    <div class="status-card{{ $cardActive }}">
                @endif
                    <span class="badge {{ $card['badge'] }}" style="font-size:.65rem; padding: 2px 7px;"><span class="badge-dot"></span>{{ $card['label'] }}</span>
                    <div style="font-size: 1.35rem; font-weight: 800; margin-top: 4px; color: var(--text);">{{ $card['count'] }}</div>
                @if($card['count'] > 0)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

{{-- Drill-down order list for the selected status card --}}
@if($selectedStatus !== null)
<div class="panel" id="order-status" style="margin-bottom: 20px; scroll-margin-top: 80px;">
    <div class="panel-head">
        <span class="panel-title">{{ $statusCards[$selectedStatus]['label'] }} {{ __('Orders') }} ({{ number_format($statusOrders->total()) }})</span>
        <div style="display:flex;gap:8px;">
            @if($statusOrders->total() > 0)
                <x-export-pdf-button :href="route('admin.dashboard.export-pdf', ['status' => $selectedStatus])" style="padding:5px 12px;font-size:.75rem;" />
            @endif
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary" style="padding:5px 12px;font-size:.75rem;">{{ __('Clear') }}</a>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Order #') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Receiver') }}</th>
                    <th>{{ __('City') }}</th>
                    <th>{{ __('Driver') }}</th>
                    <th>{{ __('COD Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $orderStatusClasses = [
                        'pending'   => 'badge-pending',
                        'assigned'  => 'badge-info',
                        'picked_up' => 'badge-info',
                        'delivered' => 'badge-active',
                        'rejected'  => 'badge-suspended',
                        'returned'  => 'badge-no',
                        'cancelled' => 'badge-suspended',
                    ];
                @endphp
                @forelse($statusOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">
                                #{{ $order->order_number }}
                            </a>
                        </td>
                        <td><div class="cell-main">{{ $order->clientProfile->company_name ?? 'N/A' }}</div></td>
                        <td>
                            <div class="cell-main">{{ $order->receiver->receiver_name ?? '—' }}</div>
                            <div class="cell-sub">{{ $order->receiver->receiver_phone ?? '' }}</div>
                        </td>
                        <td>{{ $order->receiver->city->name ?? '—' }}</td>
                        <td>
                            @if($order->driver)
                                {{ $order->driver->name }}
                            @else
                                <span class="cell-sub" style="font-style: italic;">{{ __('Unassigned') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($order->payment?->order_amount)
                                <span style="font-weight:700;color:#fbbf24;">{{ number_format($order->payment->order_amount, 2) }} JD</span>
                            @else
                                <span style="color:var(--text-dim);">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $orderStatusClasses[$order->status] ?? 'badge-no' }}">
                                <span class="badge-dot"></span>
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td><span style="font-size:.8rem;color:var(--text-dim);">{{ $order->created_at->format('d M Y') }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-dim); padding: 30px;">
                            {{ __('No orders found for this filter.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($statusOrders->hasPages())
        <div class="pagination-wrap">
            <div class="pag-info">
                {{ __('Showing') }} {{ $statusOrders->firstItem() }} {{ __('to') }} {{ $statusOrders->lastItem() }} {{ __('of') }} {{ $statusOrders->total() }} {{ __('entries') }}
            </div>
            <div class="pag-links">
                {{ $statusOrders->links() }}
            </div>
        </div>
    @endif
</div>
@endif

{{-- Bottom two-column --}}
<div class="bottom-grid">
    {{-- Pending action center --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">{{ __('Pending Action Items') }}</span>
            <span class="badge badge-pending" style="font-size:.72rem;">{{ $unassignedOrdersCount + $openTicketsCount }} {{ __('unresolved') }}</span>
        </div>
        <div class="quick-stats" style="padding: 0;">
            <!-- Unassigned Orders Alert -->
            <div style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--bdr); transition: background .12s;" onmouseenter="this.style.background='rgba(255,255,255,.01)';" onmouseleave="this.style.background='transparent';">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245,158,11,.1); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink:0;">📦</div>
                    <div>
                        <div style="font-size: .82rem; font-weight: 600; color: var(--text);">{{ __('Unassigned Shipments') }}</div>
                        <div style="font-size: .72rem; color: var(--text-dim);">{{ $unassignedOrdersCount }} {{ __('orders waiting for courier assignments') }}</div>
                    </div>
                </div>
                <div>
                    @if($unassignedOrdersCount > 0)
                        <a href="{{ route('admin.orders.index') }}" class="btn-primary" style="padding: 5px 12px; font-size: .72rem; box-shadow: none; border-radius: 6px;">{{ __('Dispatch') }}</a>
                    @else
                        <span style="font-size: .72rem; color: var(--success); font-weight: 600;">● {{ __('Cleared') }}</span>
                    @endif
                </div>
            </div>

            <!-- Open Support Tickets -->
            @forelse($openTickets as $ticket)
            <div style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--bdr); transition: background .12s;" onmouseenter="this.style.background='rgba(255,255,255,.01)';" onmouseleave="this.style.background='transparent';">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,.1); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink:0;">💬</div>
                    <div style="min-width: 0;">
                        <div style="font-size: .82rem; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ __('Ticket #') }}{{ $ticket->ticket_number }}: {{ $ticket->title }}</div>
                        <div style="font-size: .72rem; color: var(--text-dim); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ __('By') }} {{ $ticket->user->name ?? __('Client') }} • {{ $ticket->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.support.index', ['ticket' => $ticket->ticket_number]) }}" class="btn-secondary" style="padding: 5px 12px; font-size: .72rem; border-radius: 6px;">{{ __('Reply') }}</a>
                </div>
            </div>
            @empty
            <div style="padding: 30px; text-align: center; color: var(--text-dim); font-size: .84rem;">{{ __('No pending support tickets. All quiet!') }}</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Custom Toast Notification --}}
<div id="customToast" style="display: none; position: fixed; bottom: 24px; {{ app()->getLocale() === 'ar' ? 'left: 24px;' : 'right: 24px;' }} z-index: 9999; align-items: center; gap: 12px; background: rgba(12, 18, 48, 0.92); border: 1px solid var(--bdr); border-radius: 12px; padding: 14px 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); backdrop-filter: blur(8px); transform: translateY(40px); opacity: 0; transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease-out; max-width: 380px;">
    <!-- Icon -->
    <div id="customToastIcon" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold; flex-shrink: 0; border: 1px solid;"></div>
    <!-- Content -->
    <div style="flex: 1;">
        <div id="customToastTitle" style="font-size: 0.85rem; font-weight: 700; color: var(--text);"></div>
        <div id="customToastMessage" style="font-size: 0.78rem; color: var(--text-sub); margin-top: 2px; line-height: 1.3;"></div>
    </div>
</div>
@endsection

@section('scripts')
@if($canViewLiveMap && config('services.google.maps_api_key'))
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@php
$dashboardSeedDrivers = $mapDrivers->map(fn ($d) => [
    'id'   => $d->id,
    'name' => $d->user->name ?? '—',
    'lat'  => (float) $d->current_latitude,
    'lng'  => (float) $d->current_longitude,
    'ts'   => $d->location_updated_at ? $d->location_updated_at->diffForHumans() : '—',
])->values()->all();
@endphp
<script>
window.initDashboardMap = function () {
    var i18n = {
        lat: @json(__('Lat:')),
        lng: @json(__('Lng:')),
        justNow: @json(__('Just now')),
        driverCountTpl: @json(__(':count driver(s) with known location')),
    };
    var DEFAULT_CENTER = { lat: 31.9454, lng: 35.9284 }, DEFAULT_ZOOM = 9, MAX_FIT_ZOOM = 14;

    var BASE_STYLES = [
        { featureType: 'poi', stylers: [{ visibility: 'off' }] },
        { featureType: 'transit', stylers: [{ visibility: 'off' }] },
    ];
    var DARK_STYLES = BASE_STYLES.concat([
        { elementType: 'geometry', stylers: [{ color: '#0f1a3a' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#0c1230' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#8a94b0' }] },
        { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#2b3a6b' }] },
        { featureType: 'administrative.locality', elementType: 'labels.text.fill', stylers: [{ color: '#cbd5e1' }] },
        { featureType: 'landscape.natural', elementType: 'geometry', stylers: [{ color: '#111c40' }] },
        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#1e2a50' }] },
        { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#141d3d' }] },
        { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#94a3b8' }] },
        { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#2b3a6b' }] },
        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#070b1f' }] },
        { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#475569' }] },
    ]);

    function isLight() {
        return document.documentElement.classList.contains('light-theme');
    }

    var map = new google.maps.Map(document.getElementById('dashboardMap'), {
        center: DEFAULT_CENTER,
        zoom: DEFAULT_ZOOM,
        gestureHandling: 'cooperative',
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        clickableIcons: false,
        backgroundColor: isLight() ? '#e5e3df' : '#0c1230',
        styles: isLight() ? BASE_STYLES : DARK_STYLES,
    });

    document.addEventListener('themechange', function () {
        map.setOptions({ styles: isLight() ? BASE_STYLES : DARK_STYLES });
    });

    var infoWindow = new google.maps.InfoWindow({ pixelOffset: new google.maps.Size(0, -18) });
    var openMarkerId = null;
    google.maps.event.addListener(infoWindow, 'closeclick', function () { openMarkerId = null; });

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function popupHtml(m) {
        return '<div class="gmap-popup"><b>' + escapeHtml(m.name) + '</b><br>'
             + i18n.lat + ' ' + m.position.lat().toFixed(5) + '<br>'
             + i18n.lng + ' ' + m.position.lng().toFixed(5) + '<br>'
             + '<span class="ts">' + escapeHtml(m.ts) + '</span></div>';
    }

    function openPopup(m) {
        openMarkerId = m.id;
        infoWindow.setContent(popupHtml(m));
        infoWindow.setPosition(m.position);
        infoWindow.open({ map: map });
    }

    // HTML marker (initials avatar) drawn as a map overlay
    function DriverMarker(id, name, lat, lng, ts) {
        this.id = id;
        this.name = name;
        this.ts = ts;
        this.position = new google.maps.LatLng(lat, lng);
        this.div = null;
        this.setMap(map);
    }
    DriverMarker.prototype = new google.maps.OverlayView();
    DriverMarker.prototype.onAdd = function () {
        var self = this;
        this.div = document.createElement('div');
        this.div.className = 'gmap-driver-marker';
        this.div.textContent = (this.name || '?').substring(0, 2).toUpperCase();
        this.div.title = this.name || '';
        this.div.addEventListener('click', function () { openPopup(self); });
        google.maps.OverlayView.preventMapHitsAndGesturesFrom(this.div);
        this.getPanes().overlayMouseTarget.appendChild(this.div);
    };
    DriverMarker.prototype.draw = function () {
        var projection = this.getProjection();
        if (!this.div || !projection) return;
        var point = projection.fromLatLngToDivPixel(this.position);
        if (point) {
            this.div.style.left = point.x + 'px';
            this.div.style.top = point.y + 'px';
        }
    };
    DriverMarker.prototype.onRemove = function () {
        if (this.div) this.div.remove();
        this.div = null;
    };
    DriverMarker.prototype.update = function (name, lat, lng, ts) {
        this.name = name;
        this.ts = ts;
        this.position = new google.maps.LatLng(lat, lng);
        this.draw();
        if (openMarkerId === this.id) openPopup(this);
    };

    var markers = {};

    function addOrUpdateMarker(id, name, lat, lng, ts) {
        if (markers[id]) {
            markers[id].update(name, lat, lng, ts);
        } else {
            markers[id] = new DriverMarker(id, name, lat, lng, ts);
        }
    }

    var SEED = @json($dashboardSeedDrivers);
    if (SEED.length > 0) {
        var bounds = new google.maps.LatLngBounds();
        SEED.forEach(function (d) {
            addOrUpdateMarker(d.id, d.name, d.lat, d.lng, d.ts);
            bounds.extend({ lat: d.lat, lng: d.lng });
        });
        map.fitBounds(bounds, 30);
        google.maps.event.addListenerOnce(map, 'idle', function () {
            if (map.getZoom() > MAX_FIT_ZOOM) map.setZoom(MAX_FIT_ZOOM);
        });
    }

    // Real-time position updates
    var pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
        forceTLS: true,
    });

    pusher.subscribe('admin.drivers').bind('location.updated', function (data) {
        addOrUpdateMarker(data.driver_id, data.name, parseFloat(data.latitude), parseFloat(data.longitude), i18n.justNow);

        var emptyEl = document.getElementById('dashboardMapEmpty');
        if (emptyEl) emptyEl.remove();

        var countEl = document.getElementById('dashboardDriverCount');
        if (countEl) countEl.textContent = i18n.driverCountTpl.replace(':count', Object.keys(markers).length);
    });
};
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google.maps_api_key')) }}&callback=initDashboardMap&loading=async&language={{ app()->getLocale() }}"></script>
@endif
<script>
const dashboardI18n = {
    locating: @json(__('Locating...')),
    saving: @json(__('Saving...')),
    success: @json(__('Success')),
    verificationFailed: @json(__('Verification Failed')),
    checkInFailed: @json(__('Check-in failed.')),
    error: @json(__('Error')),
    networkError: @json(__('A network error occurred. Please try again.')),
};

/* Post Attendance Check-In / Check-Out */
function submitAttendance(type) {
    const btn = type === 'check-in' ? document.getElementById('dashboardCheckInBtn') : document.getElementById('dashboardCheckOutBtn');
    const statusText = document.getElementById('attendanceStatus');
    const originalText = btn.textContent;

    btn.disabled = true;
    btn.textContent = dashboardI18n.locating;

    const sendAttendanceRequest = (coords = null) => {
        btn.textContent = dashboardI18n.saving;
        const url = type === 'check-in' 
            ? "{{ route('admin.attendance.check-in') }}" 
            : "{{ route('admin.attendance.check-out') }}";
            
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ location: coords })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(dashboardI18n.success, data.message, true, () => {
                    window.location.reload();
                });
            } else {
                showToast(dashboardI18n.verificationFailed, data.message || dashboardI18n.checkInFailed, false);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(err => {
            showToast(dashboardI18n.error, dashboardI18n.networkError, false);
            btn.disabled = false;
            btn.textContent = originalText;
        });
    };

    // Get Browser Geolocation coordinates
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                sendAttendanceRequest(`${pos.coords.latitude},${pos.coords.longitude}`);
            },
            (err) => {
                // Fallback without coordinates
                sendAttendanceRequest(null);
            },
            { enableHighAccuracy: true, timeout: 6000 }
        );
    } else {
        sendAttendanceRequest(null);
    }
}

/* Working Shift active timer */
const activeTimer = document.getElementById('activeTimer');
if (activeTimer) {
    const startTime = new Date(activeTimer.dataset.start);
    setInterval(() => {
        const diffMs = new Date() - startTime;
        const diffHrs = Math.floor(diffMs / 3600000);
        const diffMins = Math.floor((diffMs % 3600000) / 60000);
        const diffSecs = Math.floor((diffMs % 60000) / 1000);
        activeTimer.textContent = 
            String(diffHrs).padStart(2, '0') + ':' + 
            String(diffMins).padStart(2, '0') + ':' + 
            String(diffSecs).padStart(2, '0');
    }, 1000);
}

/* Custom Toast Notification Functions */
let toastTimeout = null;

function showToast(title, message, isSuccess = true, callback = null) {
    const toast = document.getElementById('customToast');
    const titleEl = document.getElementById('customToastTitle');
    const msgEl = document.getElementById('customToastMessage');
    const icon = document.getElementById('customToastIcon');
    
    titleEl.textContent = title;
    msgEl.textContent = message;
    
    if (isSuccess) {
        icon.textContent = '✓';
        icon.style.color = 'var(--success)';
        icon.style.background = 'rgba(34,197,94,.1)';
        icon.style.borderColor = 'rgba(34,197,94,.2)';
        toast.style.borderColor = 'rgba(34,197,94,.25)';
    } else {
        icon.textContent = '✕';
        icon.style.color = '#f87171';
        icon.style.background = 'rgba(220,38,38,.1)';
        icon.style.borderColor = 'rgba(220,38,38,.2)';
        toast.style.borderColor = 'rgba(220,38,38,.25)';
    }
    
    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }
    
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    toastTimeout = setTimeout(() => {
        dismissToast(callback);
    }, 3000);
}

function dismissToast(callback = null) {
    const toast = document.getElementById('customToast');
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(40px)';
    
    setTimeout(() => {
        toast.style.display = 'none';
        if (typeof callback === 'function') {
            callback();
        }
    }, 300);
}
</script>
@endsection
