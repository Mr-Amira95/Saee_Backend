@extends('client.layouts.app')
@section('title', __('Reports'))
@section('page-title', __('Reports'))

@push('styles')
<style>
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; }
    .kpi-card { background: var(--card); border: 1px solid var(--bdr); border-radius: 14px; padding: 18px 20px; backdrop-filter: blur(8px); }
    .kpi-label { font-size: .7rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 8px; }
    .kpi-value { font-size: 1.9rem; font-weight: 900; letter-spacing: -.03em; line-height: 1; }
    .kpi-sub   { font-size: .77rem; color: var(--text-dim); margin-top: 6px; }
    .kpi-accent-green  { border-top: 3px solid #22c55e; }
    .kpi-accent-red    { border-top: 3px solid var(--red); }
    .kpi-accent-blue   { border-top: 3px solid #3b82f6; }
    .kpi-accent-yellow { border-top: 3px solid #f59e0b; }
    .kpi-accent-purple { border-top: 3px solid #a855f7; }
    .kpi-accent-teal   { border-top: 3px solid #14b8a6; }

    .report-grid { display: grid; grid-template-columns: 1fr 340px; gap: 16px; margin-bottom: 24px; }
    @media (max-width: 960px) { .report-grid { grid-template-columns: 1fr; } }

    .section-title { font-size: .72rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 14px; }

    /* Status donut */
    .donut-wrap { display: flex; flex-direction: column; align-items: center; gap: 16px; }
    .donut-legend { width: 100%; display: flex; flex-direction: column; gap: 8px; }
    .legend-row { display: flex; align-items: center; justify-content: space-between; font-size: .82rem; }
    .legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
    .legend-label { display: flex; align-items: center; gap: 8px; color: var(--text-sub); }
    .legend-count { font-weight: 700; color: var(--text); }

    /* City table */
    .city-bar-bg { background: rgba(255,255,255,.05); border-radius: 4px; height: 5px; flex: 1; overflow: hidden; }
    .city-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--red-dark), var(--red-lt)); }

    /* Filter presets */
    .preset-btns { display: flex; gap: 6px; flex-wrap: wrap; }
    .preset-btn { padding: 5px 12px; border-radius: 7px; font-size: .78rem; font-weight: 500; border: 1px solid var(--bdr); background: rgba(255,255,255,.03); color: var(--text-sub); cursor: pointer; text-decoration: none; transition: background .13s, color .13s, border-color .13s; }
    .preset-btn:hover, .preset-btn.active { background: rgba(220,38,38,.1); color: #fca5a5; border-color: rgba(220,38,38,.2); }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-hd">
    <div class="page-hd-left">
        <h1>{{ __('Reports') }}</h1>
        <p>{{ __('Shipment performance and analytics for your account') }}</p>
    </div>
    <div class="page-hd-right">
        <a href="{{ route('client.reports.export', request()->only('from','to')) }}" class="btn-secondary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            {{ __('Export CSV') }}
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="filter-bar" style="margin-bottom:22px;">
    <form method="GET" action="{{ route('client.reports.index') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%;">
        <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:.78rem;color:var(--text-dim);white-space:nowrap;">{{ __('From') }}</label>
            <input type="date" name="from" class="filter-input" style="min-width:0;width:140px;" value="{{ $from }}">
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:.78rem;color:var(--text-dim);white-space:nowrap;">{{ __('To') }}</label>
            <input type="date" name="to" class="filter-input" style="min-width:0;width:140px;" value="{{ $to }}">
        </div>
        <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:.83rem;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            {{ __('Apply') }}
        </button>
        <div class="preset-btns" style="margin-left:auto;">
            <a href="{{ route('client.reports.index', ['from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString()]) }}" class="preset-btn {{ ($from === now()->subDays(6)->toDateString() && $to === now()->toDateString()) ? 'active' : '' }}">{{ __('7 days') }}</a>
            <a href="{{ route('client.reports.index', ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()]) }}" class="preset-btn {{ ($from === now()->subDays(29)->toDateString() && $to === now()->toDateString()) ? 'active' : '' }}">{{ __('30 days') }}</a>
            <a href="{{ route('client.reports.index', ['from' => now()->subDays(89)->toDateString(), 'to' => now()->toDateString()]) }}" class="preset-btn {{ ($from === now()->subDays(89)->toDateString() && $to === now()->toDateString()) ? 'active' : '' }}">{{ __('90 days') }}</a>
            <a href="{{ route('client.reports.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}" class="preset-btn {{ ($from === now()->startOfMonth()->toDateString() && $to === now()->toDateString()) ? 'active' : '' }}">{{ __('This month') }}</a>
        </div>
    </form>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card kpi-accent-blue">
        <div class="kpi-label">{{ __('Total Orders') }}</div>
        <div class="kpi-value" style="color:var(--text);">{{ number_format($total) }}</div>
        <div class="kpi-sub">{{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</div>
    </div>
    <div class="kpi-card kpi-accent-green">
        <div class="kpi-label">{{ __('Delivered') }}</div>
        <div class="kpi-value" style="color:#4ade80;">{{ number_format($delivered) }}</div>
        <div class="kpi-sub">{{ __('Success rate:') }} <strong style="color:#4ade80;">{{ $successRate }}%</strong></div>
    </div>
    <div class="kpi-card kpi-accent-red">
        <div class="kpi-label">{{ __('Returned / Failed') }}</div>
        <div class="kpi-value" style="color:#f87171;">{{ number_format($returned) }}</div>
        <div class="kpi-sub">{{ $total > 0 ? round(($returned / $total) * 100, 1) : 0 }}% {{ __('of total') }}</div>
    </div>
    <div class="kpi-card kpi-accent-yellow">
        <div class="kpi-label">{{ __('Active (Pending + Transit)') }}</div>
        <div class="kpi-value" style="color:#fbbf24;">{{ number_format($pending + $inTransit) }}</div>
        <div class="kpi-sub">{{ $pending }} {{ __('pending') }} · {{ $inTransit }} {{ __('in transit') }}</div>
    </div>
    <div class="kpi-card kpi-accent-purple">
        <div class="kpi-label">{{ __('Total COD Value') }}</div>
        <div class="kpi-value" style="color:#c084fc;font-size:1.5rem;">{{ number_format($totalCod, 2) }}</div>
        <div class="kpi-sub">JD {{ __('collected from customers') }}</div>
    </div>
    <div class="kpi-card kpi-accent-teal">
        <div class="kpi-label">{{ __('Delivery Fees') }}</div>
        <div class="kpi-value" style="color:#2dd4bf;font-size:1.5rem;">{{ number_format($totalDelivery, 2) }}</div>
        <div class="kpi-sub">JD {{ __('total delivery charges') }}</div>
    </div>
</div>

{{-- Trend + Donut Row --}}
<div class="report-grid">
    {{-- Trend chart --}}
    <div class="card">
        <div class="section-title">{{ __('Daily Order Volume') }}</div>
        @php
            $trendValues = $dailyTrend->pluck('count')->toArray();
            $trendDates  = $dailyTrend->pluck('date')->toArray();
            $maxTrend    = max(1, max($trendValues));
            $dayCount    = count($trendValues);

            $trendPoints = [];
            $fillPoints  = [];
            foreach ($trendValues as $i => $val) {
                $x = $dayCount > 1 ? ($i / ($dayCount - 1)) * 580 + 10 : 295;
                $y = 120 - (($val / $maxTrend) * 90) - 10;
                $trendPoints[] = "$x,$y";
                if ($i === 0) $fillPoints[] = "10,120";
                $fillPoints[] = "$x,$y";
                if ($i === $dayCount - 1) $fillPoints[] = "$x,120";
            }
            $polyline = implode(' ', $trendPoints);
            $polygon  = implode(' ', $fillPoints);

            // Show ~6 date labels evenly spaced
            $labelStep = max(1, (int) floor($dayCount / 6));
        @endphp
        <div style="width:100%;overflow:hidden;">
            <svg viewBox="0 0 600 130" width="100%" height="130" preserveAspectRatio="none" style="overflow:visible;display:block;">
                <defs>
                    <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#dc2626" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#dc2626" stop-opacity="0.0"/>
                    </linearGradient>
                </defs>
                {{-- Grid lines --}}
                @foreach ([30, 60, 90, 120] as $gy)
                    <line x1="10" y1="{{ $gy }}" x2="590" y2="{{ $gy }}" stroke="rgba(255,255,255,.03)" stroke-width="1"/>
                @endforeach
                {{-- Fill --}}
                <polygon points="{{ $polygon }}" fill="url(#trendFill)"/>
                {{-- Line --}}
                <polyline points="{{ $polyline }}" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="filter:drop-shadow(0 2px 6px rgba(220,38,38,.3));"/>
                {{-- Dots --}}
                @foreach ($trendValues as $i => $val)
                    @if ($val > 0)
                        @php
                            $dx = $dayCount > 1 ? ($i / ($dayCount - 1)) * 580 + 10 : 295;
                            $dy = 120 - (($val / $maxTrend) * 90) - 10;
                        @endphp
                        <circle cx="{{ $dx }}" cy="{{ $dy }}" r="3" fill="var(--bg)" stroke="#ef4444" stroke-width="1.8"/>
                    @endif
                @endforeach
            </svg>
        </div>
        {{-- Date labels --}}
        <div style="display:flex;justify-content:space-between;margin-top:6px;padding:0 4px;">
            @foreach ($trendDates as $i => $d)
                @if ($i % $labelStep === 0 || $i === $dayCount - 1)
                    <span style="font-size:.65rem;color:var(--text-dim);">{{ \Carbon\Carbon::parse($d)->format('d M') }}</span>
                @endif
            @endforeach
        </div>
        {{-- Y-axis hint --}}
        <div style="font-size:.7rem;color:var(--text-dim);margin-top:8px;text-align:right;">
            {{ __('Peak:') }} {{ $maxTrend }} {{ __('orders') }}
        </div>
    </div>

    {{-- Status donut --}}
    <div class="card" style="display:flex;flex-direction:column;">
        <div class="section-title">{{ __('Status Breakdown') }}</div>
        @php
            $statusConfig = [
                'delivered'  => ['label' => __('Delivered'),   'color' => '#22c55e'],
                'pending'    => ['label' => __('Pending'),     'color' => '#f59e0b'],
                'picked_up'  => ['label' => __('In Transit'),  'color' => '#3b82f6'],
                'returned'   => ['label' => __('Returned'),    'color' => '#94a3b8'],
                'rejected'   => ['label' => __('Rejected'),    'color' => '#f87171'],
                'cancelled'  => ['label' => __('Cancelled'),   'color' => '#6b7280'],
            ];

            $donutTotal  = max(1, $total);
            $radius      = 58;
            $cx = 90; $cy = 90;
            $circumference = 2 * M_PI * $radius;
            $offset = 0;
            $segments = [];
            foreach ($statusConfig as $key => $cfg) {
                $cnt  = $statusCounts[$key] ?? 0;
                if ($cnt === 0) continue;
                $pct  = $cnt / $donutTotal;
                $dash = $circumference * $pct;
                $segments[] = compact('key', 'cnt', 'pct', 'dash', 'offset') + $cfg;
                $offset += $dash;
            }
        @endphp

        @if ($total > 0)
        <div class="donut-wrap">
            <svg viewBox="0 0 180 180" width="160" height="160" style="flex-shrink:0;">
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}" fill="none" stroke="rgba(255,255,255,.04)" stroke-width="18"/>
                @foreach ($segments as $seg)
                    <circle
                        cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $radius }}"
                        fill="none"
                        stroke="{{ $seg['color'] }}"
                        stroke-width="18"
                        stroke-dasharray="{{ number_format($seg['dash'], 4, '.', '') }} {{ number_format($circumference - $seg['dash'], 4, '.', '') }}"
                        stroke-dashoffset="{{ number_format($circumference - $seg['offset'], 4, '.', '') }}"
                        stroke-linecap="butt"
                        transform="rotate(-90 {{ $cx }} {{ $cy }})"
                    />
                @endforeach
                <text x="{{ $cx }}" y="{{ $cy - 6 }}" text-anchor="middle" fill="var(--text)" font-size="18" font-weight="800">{{ number_format($total) }}</text>
                <text x="{{ $cx }}" y="{{ $cy + 14 }}" text-anchor="middle" fill="var(--text-dim)" font-size="10">{{ __('orders') }}</text>
            </svg>
            <div class="donut-legend" style="width:100%;">
                @foreach ($segments as $seg)
                    <div class="legend-row">
                        <div class="legend-label">
                            <span class="legend-dot" style="background:{{ $seg['color'] }};"></span>
                            <span>{{ $seg['label'] }}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:.72rem;color:var(--text-dim);">{{ round($seg['pct'] * 100, 1) }}%</span>
                            <span class="legend-count">{{ $seg['cnt'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
            <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--text-dim);font-size:.87rem;padding:32px 0;">
                {{ __('No orders in this period.') }}
            </div>
        @endif
    </div>
</div>

{{-- City Breakdown --}}
@if ($cityBreakdown->count())
<div class="card" style="margin-bottom:24px;">
    <div class="section-title">{{ __('Performance by City') }}</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('City') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Delivered') }}</th>
                    <th>{{ __('Returned') }}</th>
                    <th>{{ __('Active') }}</th>
                    <th>{{ __('Success Rate') }}</th>
                    <th style="width:180px;">{{ __('Volume') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $maxCityTotal = max(1, $cityBreakdown->max('total')); @endphp
                @foreach ($cityBreakdown as $city)
                    @php
                        $cityCompleted = $city->delivered + $city->returned;
                        $cityRate = $cityCompleted > 0 ? round(($city->delivered / $cityCompleted) * 100, 1) : 0;
                        $barWidth  = round(($city->total / $maxCityTotal) * 100);
                        $rateColor = $cityRate >= 80 ? '#4ade80' : ($cityRate >= 60 ? '#fbbf24' : '#f87171');
                    @endphp
                    <tr>
                        <td class="cell-main">{{ $city->city_name }}</td>
                        <td><strong>{{ $city->total }}</strong></td>
                        <td style="color:#4ade80;font-weight:600;">{{ $city->delivered }}</td>
                        <td style="color:#f87171;font-weight:600;">{{ $city->returned }}</td>
                        <td style="color:#fbbf24;font-weight:600;">{{ $city->active }}</td>
                        <td>
                            <span style="font-weight:700;color:{{ $rateColor }};">{{ $cityRate }}%</span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="city-bar-bg">
                                    <div class="city-bar-fill" style="width:{{ $barWidth }}%;"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Summary footer --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;animation:fu .5s both;">
    <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" fill="none" stroke="#4ade80" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        <div>
            <div style="font-size:.7rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;font-weight:700;">{{ __('Success Rate') }}</div>
            <div style="font-size:1.25rem;font-weight:800;color:#4ade80;">{{ $successRate }}%</div>
        </div>
    </div>
    <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <div>
            <div style="font-size:.7rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;font-weight:700;">{{ __('Date Range') }}</div>
            <div style="font-size:.88rem;font-weight:700;color:var(--text);">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</div>
        </div>
    </div>
    <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:12px;">
        <svg width="20" height="20" fill="none" stroke="#c084fc" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <div style="font-size:.7rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.08em;font-weight:700;">{{ __('Net COD') }}</div>
            <div style="font-size:1rem;font-weight:800;color:#c084fc;">{{ number_format($totalCod - $totalDelivery, 2) }} JD</div>
        </div>
    </div>
</div>

@endsection
