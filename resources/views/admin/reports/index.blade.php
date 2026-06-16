@extends('admin.layouts.app')

@section('title', 'Reports & Exports')
@section('page-title', 'Reporting Center')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Reports Center</span>
@endsection

@section('head')
<style>
    .reports-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-top: 18px;
    }
    @media(max-width: 900px) { .reports-grid { grid-template-columns: 1fr; } }
    
    .chart-panel {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
    }

    .export-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--bdr);
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: border-color .2s;
    }
    .export-card:hover {
        border-color: rgba(220, 38, 38, 0.2);
    }
    
    .export-title {
        font-weight: 700;
        font-size: .86rem;
        color: #fff;
    }
    .export-desc {
        font-size: .74rem;
        color: var(--text-dim);
        margin-top: 2px;
    }
</style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Reporting &amp; Data Exports</h1>
            <p>Monitor real-time metrics, audit system logs, and download spreadsheet-compatible tables.</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.kpis') }}" class="btn-primary" style="box-shadow: none;">
                📊 View KPI Dashboards
            </a>
        </div>
    </div>

    {{-- Order Status Breakdown row --}}
    <div class="mini-stats" style="margin-bottom: 20px;">
        <div class="mini-stat" style="min-width: 140px;">
            <div>
                <div class="ms-val" style="color: #60a5fa;">{{ $totalOrders }}</div>
                <div class="ms-lbl">Total Logged Orders</div>
            </div>
        </div>
        <div class="mini-stat" style="min-width: 140px;">
            <div>
                <div class="ms-val" style="color: #4ade80;">{{ $statusCounts['delivered'] ?? 0 }}</div>
                <div class="ms-lbl">Delivered Shipments</div>
            </div>
        </div>
        <div class="mini-stat" style="min-width: 140px;">
            <div>
                <div class="ms-val" style="color: #fcd34d;">{{ $statusCounts['picked_up'] ?? 0 }}</div>
                <div class="ms-lbl">In-Transit / With Drivers</div>
            </div>
        </div>
        <div class="mini-stat" style="min-width: 140px;">
            <div>
                <div class="ms-val" style="color: #f87171;">{{ ($statusCounts['rejected'] ?? 0) + ($statusCounts['returned'] ?? 0) }}</div>
                <div class="ms-lbl">Rejections &amp; Returns</div>
            </div>
        </div>
    </div>

    <div class="reports-grid">
        {{-- 1. Dynamic SVG Weekly Volume Line Chart --}}
        <div class="chart-panel">
            <h3 style="font-size: .86rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 24px;">7-Day Orders Submitted Trend</h3>
            
            <div style="flex: 1; min-height: 200px; display: flex; align-items: flex-end; position: relative;">
                @if($dailyTrend->count() > 1)
                    @php
                        $maxVal = max($dailyTrend->pluck('count')->toArray());
                        $maxVal = $maxVal > 0 ? $maxVal : 10;
                        $width = 500;
                        $height = 180;
                        
                        $points = [];
                        foreach($dailyTrend as $index => $stat) {
                            $x = ($index / ($dailyTrend->count() - 1)) * $width;
                            $y = $height - (($stat->count / $maxVal) * $height * 0.8) - 10;
                            $points[] = "$x,$y";
                        }
                        $pointsStr = implode(' ', $points);
                    @endphp
                    
                    <svg viewBox="0 0 {{ $width }} {{ $height }}" width="100%" height="100%" style="overflow: visible;">
                        <!-- Chart Grid lines -->
                        <line x1="0" y1="{{ $height }}" x2="{{ $width }}" y2="{{ $height }}" stroke="rgba(255,255,255,.05)" stroke-width="1" />
                        <line x1="0" y1="{{ $height / 2 }}" x2="{{ $width }}" y2="{{ $height / 2 }}" stroke="rgba(255,255,255,.03)" stroke-width="1" />
                        <line x1="0" y1="0" x2="{{ $width }}" y2="0" stroke="rgba(255,255,255,.03)" stroke-width="1" />
                        
                        <!-- Line Path -->
                        <polyline fill="none" stroke="var(--red-lt)" stroke-width="3" points="{{ $pointsStr }}" />
                        
                        <!-- Dots and Labels -->
                        @foreach($dailyTrend as $index => $stat)
                            @php
                                $coord = explode(',', $points[$index]);
                                $cx = $coord[0];
                                $cy = $coord[1];
                            @endphp
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="5" fill="#fff" stroke="var(--red)" stroke-width="2" />
                            <text x="{{ $cx }}" y="{{ $cy - 12 }}" font-size="9" fill="var(--text-sub)" font-weight="700" text-anchor="middle">
                                {{ $stat->count }}
                            </text>
                            
                            <text x="{{ $cx }}" y="{{ $height + 15 }}" font-size="8" fill="var(--text-dim)" text-anchor="middle">
                                {{ date('d M', strtotime($stat->date)) }}
                            </text>
                        @endforeach
                    </svg>
                @else
                    <div style="margin: auto; color: var(--text-dim); font-size: .8rem; font-style: italic;">
                        Not enough data over the past week to plot the line chart.
                    </div>
                @endif
            </div>
        </div>

        {{-- 2. Export Actions Panel --}}
        <div class="chart-panel" style="gap: 12px; background: var(--card);">
            <h3 style="font-size: .86rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px;">Spreadsheet Data Exports</h3>
            
            <div class="export-card">
                <div>
                    <div class="export-title">Orders Database</div>
                    <div class="export-desc">Full details of order volumes</div>
                </div>
                <a href="{{ route('admin.reports.export', 'orders') }}" class="btn-primary" style="padding: 6px 12px; font-size: .75rem; box-shadow: none;">CSV Export</a>
            </div>

            <div class="export-card">
                <div>
                    <div class="export-title">Financial Ledger</div>
                    <div class="export-desc">Double-entry ledger entry sheets</div>
                </div>
                <a href="{{ route('admin.reports.export', 'financials') }}" class="btn-primary" style="padding: 6px 12px; font-size: .75rem; box-shadow: none;">CSV Export</a>
            </div>

            <div class="export-card">
                <div>
                    <div class="export-title">Attendance Sheets</div>
                    <div class="export-desc">Driver and staff daily check-ins</div>
                </div>
                <a href="{{ route('admin.reports.export', 'attendance') }}" class="btn-primary" style="padding: 6px 12px; font-size: .75rem; box-shadow: none;">CSV Export</a>
            </div>

            <div class="export-card">
                <div>
                    <div class="export-title">Customer Reviews</div>
                    <div class="export-desc">Driver rating metrics & comments</div>
                </div>
                <a href="{{ route('admin.reports.export', 'ratings') }}" class="btn-primary" style="padding: 6px 12px; font-size: .75rem; box-shadow: none;">CSV Export</a>
            </div>
        </div>
    </div>
@endsection
