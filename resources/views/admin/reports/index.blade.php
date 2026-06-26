@extends('admin.layouts.app')

@section('title', 'Reports & Exports')
@section('page-title', 'Reporting Center')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Reports Center</span>
@endsection

@section('head')
<style>
    .reports-main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    @media(max-width: 960px) { .reports-main-grid { grid-template-columns: 1fr; } }

    .chart-panel {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(8px);
    }
    .panel-heading {
        font-size: .76rem;
        font-weight: 700;
        color: var(--text-sub);
        text-transform: uppercase;
        letter-spacing: .09em;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .exports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 14px;
    }
    .export-card {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        transition: border-color .18s, transform .15s, box-shadow .15s;
    }
    .export-card:hover {
        border-color: rgba(220, 38, 38, 0.28);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,.25);
    }
    .export-icon {
        width: 44px;
        height: 44px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .export-title { font-weight: 700; font-size: .87rem; color: var(--text); }
    .export-desc  { font-size: .73rem; color: var(--text-dim); margin-top: 2px; line-height: 1.4; }

    /* Metric cards (reuse dashboard pattern) */
    .metric {
        background: var(--card); border: 1px solid var(--bdr); border-radius: 14px;
        padding: 20px; backdrop-filter: blur(8px);
        position: relative; overflow: hidden; transition: border-color .2s;
        animation: fu .45s both;
    }
    .metric::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 100% 0%, var(--m-icon-bg, rgba(220,38,38,.1)) 0%, transparent 60%);
        pointer-events: none;
    }
    .metric-head  { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .metric-icon  {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--m-icon-bg, rgba(220,38,38,.1));
        color: var(--m-color, #ef4444);
    }
    .metric-val { font-size: 1.65rem; font-weight: 900; letter-spacing: -.03em; line-height: 1; margin-bottom: 5px; }
    .metric-lbl { font-size: .74rem; color: var(--text-dim); font-weight: 500; }
    .metric-badge { font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 100px; }

    .status-row { display: flex; align-items: center; gap: 10px; }
    .status-dot  { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
</style>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Reporting &amp; Data Exports</h1>
            <p>Live metrics, operational audits, and one-click spreadsheet exports.</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.kpis') }}" class="btn-primary" style="box-shadow: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                KPI Dashboards
            </a>
        </div>
    </div>

    {{-- Metric Cards Row --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; margin-bottom: 20px;">

        <div class="metric" style="--m-color:#60a5fa;--m-icon-bg:rgba(59,130,246,.12);animation-delay:.05s">
            <div class="metric-head">
                <div class="metric-icon">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span style="font-size:.68rem;color:var(--text-dim);font-weight:600;">All Time</span>
            </div>
            <div class="metric-val">{{ number_format($totalOrders) }}</div>
            <div class="metric-lbl">Total Orders</div>
        </div>

        <div class="metric" style="--m-color:#4ade80;--m-icon-bg:rgba(34,197,94,.12);animation-delay:.1s">
            <div class="metric-head">
                <div class="metric-icon" style="color:#4ade80">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span style="font-size:.68rem;color:#4ade80;font-weight:700;">✓ Done</span>
            </div>
            <div class="metric-val" style="color:#4ade80;">{{ number_format($statusCounts['delivered'] ?? 0) }}</div>
            <div class="metric-lbl">Delivered</div>
        </div>

        <div class="metric" style="--m-color:#60a5fa;--m-icon-bg:rgba(59,130,246,.12);animation-delay:.15s">
            <div class="metric-head">
                <div class="metric-icon" style="color:#60a5fa">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span style="font-size:.68rem;color:#60a5fa;font-weight:700;">Live</span>
            </div>
            <div class="metric-val" style="color:#60a5fa;">{{ number_format($statusCounts['picked_up'] ?? 0) }}</div>
            <div class="metric-lbl">In Transit</div>
        </div>

        <div class="metric" style="--m-color:#fbbf24;--m-icon-bg:rgba(245,158,11,.12);animation-delay:.2s">
            <div class="metric-head">
                <div class="metric-icon" style="color:#fbbf24">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:.68rem;color:#fbbf24;font-weight:700;">Queued</span>
            </div>
            <div class="metric-val" style="color:#fbbf24;">{{ number_format($statusCounts['pending'] ?? 0) }}</div>
            <div class="metric-lbl">Pending Pickup</div>
        </div>

        <div class="metric" style="--m-color:#f87171;--m-icon-bg:rgba(239,68,68,.12);animation-delay:.25s">
            <div class="metric-head">
                <div class="metric-icon" style="color:#f87171">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:.68rem;color:#f87171;font-weight:700;">Failed</span>
            </div>
            <div class="metric-val" style="color:#f87171;">{{ number_format(($statusCounts['rejected'] ?? 0) + ($statusCounts['returned'] ?? 0)) }}</div>
            <div class="metric-lbl">Rejected &amp; Returns</div>
        </div>

        <div class="metric" style="--m-color:#c084fc;--m-icon-bg:rgba(168,85,247,.12);animation-delay:.3s">
            <div class="metric-head">
                <div class="metric-icon" style="color:#c084fc">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span style="font-size:.68rem;color:var(--text-dim);font-weight:600;">Active</span>
            </div>
            <div class="metric-val" style="color:#c084fc;">{{ number_format($activeDrivers) }}</div>
            <div class="metric-lbl">Active Drivers</div>
        </div>

        <div class="metric" style="--m-color:#34d399;--m-icon-bg:rgba(16,185,129,.12);animation-delay:.35s">
            <div class="metric-head">
                <div class="metric-icon" style="color:#34d399">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span style="font-size:.68rem;color:var(--text-dim);font-weight:600;">Onboarded</span>
            </div>
            <div class="metric-val" style="color:#34d399;">{{ number_format($activeClients) }}</div>
            <div class="metric-lbl">Client Companies</div>
        </div>

    </div>

    {{-- Charts Row --}}
    <div class="reports-main-grid">

        {{-- 7-Day Trend Chart --}}
        <div class="chart-panel" style="display: flex; flex-direction: column;">
            <div class="panel-heading">
                <span>7-Day Orders Volume Trend</span>
                <span style="font-size:.7rem;color:var(--text-dim);font-weight:500;">{{ now()->subDays(6)->format('d M') }} – {{ now()->format('d M, Y') }}</span>
            </div>

            <div style="flex: 1; min-height: 210px; display: flex; align-items: flex-end; position: relative; padding-left: 28px;">
                @php
                    $maxVal  = max(max($dailyTrend->pluck('count')->toArray()), 1);
                    $svgW    = 520;
                    $svgH    = 190;
                    $padTop  = 20;
                    $cnt     = $dailyTrend->count();
                    $points  = [];

                    foreach ($dailyTrend as $idx => $stat) {
                        $x        = $cnt > 1 ? ($idx / ($cnt - 1)) * $svgW : $svgW / 2;
                        $y        = $svgH - $padTop - (($stat->count / $maxVal) * ($svgH - $padTop - 10)) - 6;
                        $points[] = "$x,$y";
                    }

                    $firstX  = explode(',', $points[0])[0];
                    $lastX   = explode(',', end($points))[0];
                    $areaD   = 'M ' . implode(' L ', $points) . " L {$lastX},{$svgH} L {$firstX},{$svgH} Z";
                    $lineStr = implode(' ', $points);
                @endphp

                <svg viewBox="-28 0 {{ $svgW + 30 }} {{ $svgH + 24 }}" width="100%" height="100%" style="overflow: visible;">
                    <defs>
                        <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="var(--red-lt)" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="var(--red-lt)" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    {{-- Y-axis grid lines --}}
                    @foreach([0, 0.25, 0.5, 0.75, 1] as $frac)
                        @php $gy = $svgH - $padTop - $frac * ($svgH - $padTop - 16) - 6; @endphp
                        <line x1="0" y1="{{ $gy }}" x2="{{ $svgW }}" y2="{{ $gy }}" stroke="rgba(255,255,255,.04)" stroke-width="1"/>
                        <text x="-4" y="{{ $gy + 3.5 }}" font-size="8" fill="var(--text-dim)" text-anchor="end">{{ round($maxVal * $frac) }}</text>
                    @endforeach
                    <line x1="0" y1="{{ $svgH }}" x2="{{ $svgW }}" y2="{{ $svgH }}" stroke="rgba(255,255,255,.06)" stroke-width="1"/>

                    {{-- Gradient fill area --}}
                    <path d="{{ $areaD }}" fill="url(#areaGrad)"/>

                    {{-- Trend line --}}
                    <polyline fill="none" stroke="var(--red-lt)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" points="{{ $lineStr }}"/>

                    {{-- Data points + labels --}}
                    @foreach($dailyTrend as $idx => $stat)
                        @php
                            [$cx, $cy] = explode(',', $points[$idx]);
                        @endphp
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4.5" fill="var(--bg)" stroke="var(--red-lt)" stroke-width="2"/>
                        @if($stat->count > 0)
                            <text x="{{ $cx }}" y="{{ $cy - 11 }}" font-size="9" fill="var(--text-sub)" font-weight="700" text-anchor="middle">{{ $stat->count }}</text>
                        @endif
                        <text x="{{ $cx }}" y="{{ $svgH + 17 }}" font-size="8" fill="var(--text-dim)" text-anchor="middle">{{ date('d M', strtotime($stat->date)) }}</text>
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Status Distribution --}}
        <div class="chart-panel" style="display: flex; flex-direction: column;">
            <div class="panel-heading">Status Distribution</div>

            @php
                $total = max(array_sum($statusCounts), 1);
                $dist  = [
                    ['key' => 'delivered',  'label' => 'Delivered',    'color' => '#4ade80'],
                    ['key' => 'pending',    'label' => 'Pending',      'color' => '#fbbf24'],
                    ['key' => 'picked_up',  'label' => 'In Transit',   'color' => '#60a5fa'],
                    ['key' => 'rejected',   'label' => 'Rejected',     'color' => '#f87171'],
                    ['key' => 'returned',   'label' => 'Returned',     'color' => '#fb923c'],
                    ['key' => 'cancelled',  'label' => 'Cancelled',    'color' => '#94a3b8'],
                ];
            @endphp

            {{-- Segmented progress bar --}}
            <div style="height: 10px; display: flex; border-radius: 6px; overflow: hidden; background: rgba(255,255,255,.04); margin-bottom: 20px;">
                @foreach($dist as $s)
                    @php $w = (($statusCounts[$s['key']] ?? 0) / $total) * 100; @endphp
                    @if($w > 0)
                        <div style="width:{{ $w }}%; background:{{ $s['color'] }};" title="{{ $s['label'] }}: {{ round($w,1) }}%"></div>
                    @endif
                @endforeach
            </div>

            {{-- Legend rows --}}
            <div style="display: flex; flex-direction: column; gap: 11px; flex: 1;">
                @foreach($dist as $s)
                    @php
                        $cnt = $statusCounts[$s['key']] ?? 0;
                        $pct = round(($cnt / $total) * 100, 1);
                    @endphp
                    <div class="status-row">
                        <div class="status-dot" style="background:{{ $s['color'] }};"></div>
                        <span style="font-size:.82rem;color:var(--text-sub);flex:1;">{{ $s['label'] }}</span>
                        <strong style="font-size:.84rem;color:var(--text);">{{ number_format($cnt) }}</strong>
                        <span style="font-size:.72rem;color:var(--text-dim);width:42px;text-align:right;">{{ $pct }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Data Exports Section --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <div>
            <h2 style="font-size:.9rem;font-weight:800;letter-spacing:-.01em;">Spreadsheet Data Exports</h2>
            <p style="font-size:.74rem;color:var(--text-dim);margin-top:2px;">CSV format, directly importable into Excel or Google Sheets.</p>
        </div>
    </div>

    <div class="exports-grid">

        <a href="{{ route('admin.reports.export', 'orders') }}" class="export-card">
            <div class="export-icon" style="background:rgba(59,130,246,.12);color:#60a5fa;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <div style="flex:1;">
                <div class="export-title">Orders Database</div>
                <div class="export-desc">All orders with driver, client, city, payment &amp; status details</div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        </a>

        <a href="{{ route('admin.reports.export', 'financials') }}" class="export-card">
            <div class="export-icon" style="background:rgba(34,197,94,.12);color:#4ade80;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div style="flex:1;">
                <div class="export-title">Financial Ledger</div>
                <div class="export-desc">Double-entry ledger entries, driver settlements &amp; payouts</div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        </a>

        <a href="{{ route('admin.reports.export', 'attendance') }}" class="export-card">
            <div class="export-icon" style="background:rgba(245,158,11,.12);color:#fbbf24;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div style="flex:1;">
                <div class="export-title">Attendance Sheets</div>
                <div class="export-desc">Driver &amp; staff check-in/out logs with GPS coordinates</div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        </a>

        <a href="{{ route('admin.reports.export', 'ratings') }}" class="export-card">
            <div class="export-icon" style="background:rgba(251,191,36,.12);color:#fbbf24;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div style="flex:1;">
                <div class="export-title">Customer Reviews</div>
                <div class="export-desc">Driver ratings, star scores &amp; written customer comments</div>
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text-dim);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        </a>

    </div>
@endsection
