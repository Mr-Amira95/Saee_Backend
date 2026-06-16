@extends('admin.layouts.app')

@section('title', 'KPI Management')
@section('page-title', 'KPI Performance Metrics')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.reports.index') }}">Reports Center</a>
    <span class="sep">/</span>
    <span class="current">KPI Insights</span>
@endsection

@section('head')
<style>
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .kpi-card {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kpi-title {
        font-size: .75rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 6px;
    }

    .kpi-val {
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: -.03em;
        line-height: 1.1;
    }

    .kpi-pie {
        position: relative;
        width: 66px;
        height: 66px;
    }

    /* Driver Leaderboard style */
    .leader-row td {
        vertical-align: middle;
    }
    
    .progress-bar-wrap {
        height: 6px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
        overflow: hidden;
        flex: 1;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
        width: 0;
        transition: width 1s ease-out;
    }
</style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>KPI Performance Insights</h1>
            <p>Track delivery success rates, return rates, driver efficiencies, and customer feedback trends.</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn-secondary">
                ← Back to Reports Center
            </a>
        </div>
    </div>

    {{-- KPI Cards Row --}}
    <div class="kpi-row">
        
        {{-- Success Rate KPI --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Delivery Success Rate</div>
                <div class="kpi-val" style="color: #4ade80;">{{ $successRate }}%</div>
                <div style="font-size: .72rem; color: var(--text-sub); margin-top: 6px;">Delivered vs Total Terminated</div>
            </div>
            
            <div class="kpi-pie">
                <svg viewBox="0 0 36 36" width="100%" height="100%">
                    <path stroke="rgba(255, 255, 255, 0.05)" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path stroke="#22c55e" stroke-width="3" stroke-dasharray="{{ $successRate }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
            </div>
        </div>

        {{-- Return Rate KPI --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Rejections &amp; Return Rate</div>
                <div class="kpi-val" style="color: {{ $returnRate > 15.0 ? 'var(--red-lt)' : '#fcd34d' }};">{{ $returnRate }}%</div>
                <div style="font-size: .72rem; color: var(--text-sub); margin-top: 6px;">Returned/Rejected orders ratio</div>
            </div>
            
            <div class="kpi-pie">
                <svg viewBox="0 0 36 36" width="100%" height="100%">
                    <path stroke="rgba(255, 255, 255, 0.05)" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path stroke="{{ $returnRate > 15.0 ? '#ef4444' : '#f59e0b' }}" stroke-width="3" stroke-dasharray="{{ $returnRate }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
            </div>
        </div>

        {{-- Customer Satisfaction KPI --}}
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Customer Satisfaction</div>
                <div class="kpi-val" style="color: #fbbf24; display: flex; align-items: center; gap: 6px;">
                    {{ $avgSatisfaction }} <span style="font-size: 1.4rem;">★</span>
                </div>
                <div style="font-size: .72rem; color: var(--text-sub); margin-top: 6px;">Overall mean star ratings</div>
            </div>
            
            <div class="kpi-pie">
                @php
                    $satPercentage = ($avgSatisfaction / 5) * 100;
                @endphp
                <svg viewBox="0 0 36 36" width="100%" height="100%">
                    <path stroke="rgba(255, 255, 255, 0.05)" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path stroke="#fbbf24" stroke-width="3" stroke-dasharray="{{ $satPercentage }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
            </div>
        </div>

    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        {{-- Driver Performance Leaderboard --}}
        <div class="table-card" style="height: fit-content;">
            <div style="padding: 16px; border-bottom: 1px solid var(--bdr);">
                <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Active Drivers Performance Ranking</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Avg Rating</th>
                            <th>Success Rate</th>
                            <th>Avg Transit Hours</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $d)
                            <tr class="leader-row">
                                <td>
                                    <div class="cell-main">{{ $d['driver']->name }}</div>
                                    <div class="cell-sub">{{ $d['driver']->phone }}</div>
                                </td>
                                <td>
                                    <strong style="color: #fbbf24;">{{ number_format($d['rating'], 1) }} ★</strong>
                                </td>
                                <td>
                                    <strong style="color: #4ade80;">{{ number_format($d['success_rate'], 1) }}%</strong>
                                </td>
                                <td>
                                    <span>{{ $d['transit_hours'] !== 'N/A' ? number_format($d['transit_hours'], 1) . ' hrs' : 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="act-btns" style="justify-content: center;">
                                        <a href="{{ route('admin.drivers.show', $d['driver']) }}" class="btn-secondary" style="padding: 6px 12px; font-size: .75rem;">
                                            Audit Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                    No active drivers in system.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Stars Distribution breakdown panel --}}
        <div class="table-card" style="padding: 24px; display: flex; flex-direction: column; gap: 16px; background: var(--card); height: fit-content;">
            <h3 style="font-size: .86rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: .08em; border-bottom: 1px solid var(--bdr); padding-bottom: 10px;">Ratings Distribution</h3>
            
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @for($star = 5; $star >= 1; $star--)
                    @php
                        $count = $starsBreakdown[$star] ?? 0;
                        $totalRatings = array_sum($starsBreakdown);
                        $percent = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                    @endphp
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: .8rem; font-weight: 700; width: 45px; text-align: right; color: var(--text-sub);">
                            {{ $star }} Star
                        </span>
                        
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width: {{ $percent }}%; background: #fbbf24;"></div>
                        </div>
                        
                        <span style="font-size: .76rem; width: 35px; text-align: left; color: var(--text-dim);">
                            {{ $count }}
                        </span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
@endsection
