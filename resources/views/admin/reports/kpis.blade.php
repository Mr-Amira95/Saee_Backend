@extends('admin.layouts.app')

@section('title', 'KPI Performance Metrics')
@section('page-title', 'KPI Dashboard')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.reports.index') }}">Reports Center</a>
    <span class="sep">/</span>
    <span class="current">KPI Insights</span>
@endsection

@section('head')
<style>
    .kpi-cards-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 18px;
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
        gap: 16px;
        animation: fu .45s both;
    }
    .kpi-label {
        font-size: .72rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .09em;
        margin-bottom: 8px;
    }
    .kpi-val {
        font-size: 2.2rem;
        font-weight: 900;
        letter-spacing: -.035em;
        line-height: 1;
    }
    .kpi-sub { font-size: .73rem; color: var(--text-sub); margin-top: 7px; }
    .kpi-ring { position: relative; width: 72px; height: 72px; flex-shrink: 0; }

    /* Progress bars */
    .prog-wrap {
        height: 6px;
        background: rgba(255,255,255,.05);
        border-radius: 4px;
        overflow: hidden;
        flex: 1;
        min-width: 60px;
    }
    .prog-fill {
        height: 100%;
        border-radius: 4px;
        width: 0;
        transition: width 1s cubic-bezier(.4,0,.2,1) .3s;
    }

    /* Ratings distribution */
    .ratings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .panel-card {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 14px;
        overflow: hidden;
        backdrop-filter: blur(8px);
        animation: fu .45s .2s both;
    }
    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--bdr);
    }
    .panel-title {
        font-size: .78rem;
        font-weight: 700;
        color: var(--text-sub);
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .star-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 20px;
    }
    .star-label { font-size: .79rem; font-weight: 700; color: var(--text-sub); width: 44px; flex-shrink: 0; }
    .star-count { font-size: .74rem; color: var(--text-dim); width: 32px; text-align: right; flex-shrink: 0; }
</style>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>KPI Performance Insights</h1>
            <p>Delivery success rates, return rates, driver rankings, and customer satisfaction.</p>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('admin.reports.ratings') }}" class="btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Ratings List
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Reports Center
            </a>
        </div>
    </div>

    {{-- Summary strip --}}
    <div class="mini-stats" style="margin-bottom:20px;">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(59,130,246,.12);">
                <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="mini-stat-val" style="color:#60a5fa;">{{ number_format($totalOrders) }}</div>
                <div class="mini-stat-lbl">Total Orders</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(34,197,94,.12);">
                <svg width="16" height="16" fill="none" stroke="#4ade80" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <div class="mini-stat-val" style="color:#4ade80;">{{ number_format($deliveredCount) }}</div>
                <div class="mini-stat-lbl">Delivered</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(239,68,68,.12);">
                <svg width="16" height="16" fill="none" stroke="#f87171" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="mini-stat-val" style="color:#f87171;">{{ number_format($failedCount) }}</div>
                <div class="mini-stat-lbl">Rejected &amp; Returned</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(251,191,36,.12);">
                <svg width="16" height="16" fill="none" stroke="#fbbf24" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div>
                <div class="mini-stat-val" style="color:#fbbf24;">{{ number_format($totalRatings) }}</div>
                <div class="mini-stat-lbl">Customer Ratings</div>
            </div>
        </div>
    </div>

    {{-- KPI Ring Cards --}}
    <div class="kpi-cards-row">

        {{-- Delivery Success Rate --}}
        <div class="kpi-card" style="animation-delay:.05s">
            <div style="flex:1;">
                <div class="kpi-label">Delivery Success Rate</div>
                <div class="kpi-val" style="color:#4ade80;">{{ $successRate }}%</div>
                <div class="kpi-sub">
                    @if($successRate >= 90)
                        <span style="color:#4ade80;font-weight:600;">● Excellent</span> — above target
                    @elseif($successRate >= 75)
                        <span style="color:#fbbf24;font-weight:600;">● Good</span> — within range
                    @else
                        <span style="color:#f87171;font-weight:600;">● Needs attention</span>
                    @endif
                </div>
                <div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;">{{ number_format($deliveredCount) }} of {{ number_format($deliveredCount + $failedCount) }} completed</div>
            </div>
            <div class="kpi-ring">
                <svg viewBox="0 0 36 36" width="100%" height="100%">
                    <path stroke="rgba(255,255,255,.05)" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path stroke="#22c55e" stroke-width="3" stroke-dasharray="{{ min($successRate, 100) }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <text x="18" y="20.5" font-size="7.5" fill="#4ade80" font-weight="800" text-anchor="middle">{{ $successRate }}%</text>
                </svg>
            </div>
        </div>

        {{-- Return Rate --}}
        @php
            $rColor  = $returnRate > 20 ? '#ef4444' : ($returnRate > 10 ? '#f59e0b' : '#22c55e');
            $rStroke = $returnRate > 20 ? '#ef4444' : ($returnRate > 10 ? '#f59e0b' : '#22c55e');
            $rLabel  = $returnRate > 20 ? '● High — investigate' : ($returnRate > 10 ? '● Moderate — monitor' : '● Low — on track');
            $rLColor = $returnRate > 20 ? '#f87171' : ($returnRate > 10 ? '#fbbf24' : '#4ade80');
        @endphp
        <div class="kpi-card" style="animation-delay:.1s">
            <div style="flex:1;">
                <div class="kpi-label">Rejection &amp; Return Rate</div>
                <div class="kpi-val" style="color:{{ $rColor }};">{{ $returnRate }}%</div>
                <div class="kpi-sub"><span style="color:{{ $rLColor }};font-weight:600;">{{ $rLabel }}</span></div>
                <div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;">{{ number_format($failedCount) }} of {{ number_format($deliveredCount + $failedCount) }} completed</div>
            </div>
            <div class="kpi-ring">
                <svg viewBox="0 0 36 36" width="100%" height="100%">
                    <path stroke="rgba(255,255,255,.05)" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path stroke="{{ $rStroke }}" stroke-width="3" stroke-dasharray="{{ min($returnRate, 100) }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <text x="18" y="20.5" font-size="7.5" fill="{{ $rColor }}" font-weight="800" text-anchor="middle">{{ $returnRate }}%</text>
                </svg>
            </div>
        </div>

        {{-- Customer Satisfaction --}}
        @php $satPct = ($avgSatisfaction / 5) * 100; @endphp
        <div class="kpi-card" style="animation-delay:.15s">
            <div style="flex:1;">
                <div class="kpi-label">Customer Satisfaction</div>
                <div class="kpi-val" style="color:#fbbf24;display:flex;align-items:center;gap:8px;">
                    {{ $avgSatisfaction }} <span style="font-size:1.5rem;">★</span>
                </div>
                <div class="kpi-sub">
                    @if($avgSatisfaction >= 4.5) <span style="color:#4ade80;font-weight:600;">● Excellent</span>
                    @elseif($avgSatisfaction >= 3.5) <span style="color:#fbbf24;font-weight:600;">● Good</span>
                    @else <span style="color:#f87171;font-weight:600;">● Needs work</span>
                    @endif
                </div>
                <div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;">{{ number_format($totalRatings) }} {{ Str::plural('review', $totalRatings) }}</div>
            </div>
            <div class="kpi-ring">
                <svg viewBox="0 0 36 36" width="100%" height="100%">
                    <path stroke="rgba(255,255,255,.05)" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path stroke="#f59e0b" stroke-width="3" stroke-dasharray="{{ round($satPct, 1) }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <text x="18" y="20.5" font-size="7" fill="#fbbf24" font-weight="800" text-anchor="middle">{{ $avgSatisfaction }}/5</text>
                </svg>
            </div>
        </div>

    </div>

    {{-- Full-width Driver Performance Leaderboard --}}
    <div class="table-card" style="animation: fu .45s .2s both;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;">
            <span class="panel-title" style="font-size:.82rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Active Drivers Performance Ranking</span>
            <span style="font-size:.72rem;color:var(--text-dim);">{{ $drivers->count() }} active {{ Str::plural('driver', $drivers->count()) }}</span>
        </div>

        @if($drivers->isEmpty())
            <div class="empty-state">
                <svg width="38" height="38" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h3>No Active Drivers</h3>
                <p>Activate drivers to see their performance ranking here.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width:46px;">#</th>
                            <th>Driver</th>
                            <th style="text-align:center;">Avg Rating</th>
                            <th style="text-align:center;">Delivered Orders</th>
                            <th style="text-align:center;">Rejected &amp; Returned</th>
                            <th style="min-width:180px;">Success Rate</th>
                            <th style="width:80px;text-align:center;">KPI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $rank => $d)
                            @php
                                $n          = $rank + 1;
                                $rankBg     = $n === 1 ? 'rgba(251,191,36,.14)' : ($n === 2 ? 'rgba(148,163,184,.1)' : ($n === 3 ? 'rgba(251,146,60,.1)' : 'rgba(255,255,255,.04)'));
                                $rankColor  = $n === 1 ? '#fbbf24' : ($n === 2 ? '#94a3b8' : ($n === 3 ? '#fb923c' : 'var(--text-dim)'));
                                $rateColor  = $d['success_rate'] >= 90 ? '#4ade80' : ($d['success_rate'] >= 70 ? '#fbbf24' : '#f87171');
                            @endphp
                            <tr>
                                <td>
                                    <div style="width:28px;height:28px;border-radius:7px;background:{{ $rankBg }};color:{{ $rankColor }};display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;">
                                        {{ $n <= 3 ? ['🥇','🥈','🥉'][$n - 1] : $n }}
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-main">{{ $d['driver']->name }}</div>
                                    <div class="cell-sub">{{ $d['driver']->phone }}</div>
                                </td>
                                <td style="text-align:center;">
                                    <strong style="color:#fbbf24;">{{ number_format($d['rating'], 1) }} ★</strong>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-size:.9rem;font-weight:700;color:#4ade80;">{{ number_format($d['delivered_count']) }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-size:.9rem;font-weight:700;color:{{ $d['failed_count'] > 0 ? '#f87171' : 'var(--text-dim)' }};">{{ number_format($d['failed_count']) }}</span>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="prog-wrap">
                                            <div class="prog-fill" style="width:{{ $d['success_rate'] }}%;background:{{ $rateColor }};"></div>
                                        </div>
                                        <span style="font-size:.8rem;font-weight:700;color:{{ $rateColor }};width:46px;text-align:right;flex-shrink:0;">{{ number_format($d['success_rate'], 1) }}%</span>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('admin.reports.driver-kpi', $d['driver']) }}" class="act-btn act-view" title="View driver KPI">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Ratings Distribution (below the full-width table) --}}
    @if($totalRatings > 0)
    @php $totalR = array_sum($starsBreakdown); @endphp
    <div class="ratings-grid">

        {{-- Stars distribution bar chart --}}
        <div class="panel-card" style="animation-delay:.3s;">
            <div class="panel-head">
                <span class="panel-title">Ratings Distribution</span>
                <span class="badge badge-pending" style="font-size:.68rem;">{{ number_format($totalRatings) }} total</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--bdr);">
                <span style="font-size:2.4rem;font-weight:900;color:#fbbf24;letter-spacing:-.04em;">{{ $avgSatisfaction }}</span>
                <div>
                    <div style="font-size:1.05rem;color:#fbbf24;">
                        @for($i=1;$i<=5;$i++) {{ $i <= round($avgSatisfaction) ? '★' : '☆' }} @endfor
                    </div>
                    <div style="font-size:.7rem;color:var(--text-dim);margin-top:2px;">{{ number_format($totalR) }} {{ Str::plural('review', $totalR) }}</div>
                </div>
            </div>
            <div style="padding:8px 0;">
                @for($star = 5; $star >= 1; $star--)
                    @php
                        $cnt      = $starsBreakdown[$star] ?? 0;
                        $pct      = $totalR > 0 ? ($cnt / $totalR) * 100 : 0;
                        $barColor = $star >= 4 ? '#4ade80' : ($star === 3 ? '#fbbf24' : '#f87171');
                    @endphp
                    <div class="star-row">
                        <span class="star-label">{{ $star }} ★</span>
                        <div class="prog-wrap">
                            <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                        </div>
                        <span class="star-count">{{ $cnt }}</span>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Link to full ratings list --}}
        <div class="panel-card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 24px;text-align:center;animation-delay:.35s;">
            <div style="width:52px;height:52px;border-radius:14px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.15);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <svg width="24" height="24" fill="none" stroke="#fbbf24" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <h3 style="font-size:1rem;font-weight:800;margin-bottom:6px;">Customer Ratings Log</h3>
            <p style="font-size:.82rem;color:var(--text-sub);line-height:1.55;margin-bottom:20px;">Browse, filter, and search all {{ number_format($totalRatings) }} customer reviews by driver, star rating, or date range.</p>
            <a href="{{ route('admin.reports.ratings') }}" class="btn-primary" style="box-shadow:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                View All Ratings
            </a>
        </div>

    </div>
    @endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.prog-fill').forEach(function (el) {
        const target = el.style.width;
        el.style.width = '0';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { el.style.width = target; });
        });
    });
});
</script>
@endsection
