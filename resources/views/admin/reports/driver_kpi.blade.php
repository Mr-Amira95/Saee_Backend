@extends('admin.layouts.app')

@section('title', 'Driver KPI: ' . $driver->name)
@section('page-title', 'Driver Performance')

@section('breadcrumb')
    <span class="sep">/</span>
    @if(auth()->user()->hasAdminAction('reports.center'))
    <a href="{{ route('admin.reports.index') }}">Reports Center</a>
    <span class="sep">/</span>
    @endif
    @if(auth()->user()->hasAdminAction('reports.kpi_insights'))
    <a href="{{ route('admin.reports.kpis') }}">KPI Insights</a>
    <span class="sep">/</span>
    @endif
    <span class="current">{{ $driver->name }}</span>
@endsection

@section('head')
<style>
    .driver-details-wrap {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    @media(max-width: 900px) {
        .driver-details-wrap {
            grid-template-columns: 1fr;
        }
    }
    .profile-sidebar-card {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .driver-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--red-deep), var(--red));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 16px;
        box-shadow: 0 4px 14px rgba(220,38,38,.2);
    }
    .profile-sidebar-card h2 {
        font-size: 1.15rem;
        font-weight: 800;
        margin-bottom: 4px;
        letter-spacing: -.02em;
    }
    .profile-sidebar-card .phone-sub {
        font-size: .82rem;
        color: var(--text-sub);
        margin-bottom: 14px;
    }
    .profile-meta-list {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 10px;
        border-top: 1px solid var(--bdr);
        padding-top: 14px;
        margin-top: 10px;
        text-align: left;
    }
    .meta-list-item {
        display: flex;
        justify-content: space-between;
        font-size: .78rem;
        line-height: 1.4;
    }
    .meta-list-item span:first-child {
        color: var(--text-dim);
    }
    .meta-list-item span:last-child {
        color: var(--text);
        font-weight: 600;
    }

    .kpi-cards-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .kpi-card {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 20px;
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        animation: fu .45s both;
    }
    .kpi-label {
        font-size: .7rem;
        font-weight: 700;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 6px;
    }
    .kpi-val {
        font-size: 1.8rem;
        font-weight: 900;
        letter-spacing: -.03em;
        line-height: 1.1;
    }
    .kpi-ring { position: relative; width: 56px; height: 56px; flex-shrink: 0; }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    @media(max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    .chart-panel {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        padding: 20px;
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
    }
    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        font-size: .78rem;
        font-weight: 700;
        color: var(--text-sub);
        text-transform: uppercase;
        letter-spacing: .08em;
    }
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
    .status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: .8rem;
    }
    .status-row:last-child {
        margin-bottom: 0;
    }

    .ratings-list-card {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 16px;
        overflow: hidden;
        backdrop-filter: blur(8px);
    }
    .review-item {
        padding: 16px 20px;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .review-item:last-child {
        border-bottom: none;
    }
    .review-hd {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .review-stars {
        color: #fbbf24;
        font-size: 1rem;
        letter-spacing: 1px;
    }
    .review-comment {
        font-size: .83rem;
        color: var(--text-sub);
        line-height: 1.5;
        font-style: italic;
    }
</style>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Driver Performance Profile</h1>
            <p>KPI metrics, 30-day order trends, status breakdown, and recent customer reviews for {{ $driver->name }}.</p>
        </div>
        <div style="display:flex;gap:10px;">
            @if(auth()->user()->hasAdminAction('reports.rating'))
            <a href="{{ route('admin.reports.ratings', ['driver' => $driver->id]) }}" class="btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                View Ratings Log
            </a>
            @endif
            @if(auth()->user()->hasAdminAction('reports.kpi_insights'))
            <a href="{{ route('admin.reports.kpis') }}" class="btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                KPI Leaderboard
            </a>
            @endif
        </div>
    </div>

    <div class="driver-details-wrap">
        {{-- Profile Card Sidebar --}}
        <div class="profile-sidebar-card">
            <div class="driver-avatar-large">
                {{ strtoupper(substr($driver->name, 0, 2)) }}
            </div>
            <h2>{{ $driver->name }}</h2>
            <div class="phone-sub">{{ $driver->phone }}</div>
            
            <span class="badge {{ $driver->status === 'active' ? 'badge-active' : 'badge-suspended' }}">
                <span class="badge-dot"></span> {{ ucfirst($driver->status) }}
            </span>

            <div class="profile-meta-list">
                <div class="meta-list-item">
                    <span>Email</span>
                    <span>{{ $driver->email }}</span>
                </div>
                <div class="meta-list-item">
                    <span>National ID</span>
                    <span>{{ $profile->national_id ?? 'N/A' }}</span>
                </div>
                <div class="meta-list-item">
                    <span>Vehicle Plate</span>
                    <span>{{ $profile->vehicle_plate ?? 'N/A' }}</span>
                </div>
                <div class="meta-list-item">
                    <span>Vehicle Type</span>
                    <span>{{ $profile->vehicle_type ?? 'N/A' }}</span>
                </div>
                <div class="meta-list-item">
                    <span>Salary Bracket</span>
                    <span>{{ $profile->basic_salary ? $profile->basic_salary . ' JD' : 'N/A' }}</span>
                </div>
                <div class="meta-list-item">
                    <span>Availability</span>
                    <span style="color: {{ ($profile->is_available ?? false) ? '#4ade80' : '#f87171' }};">
                        {{ ($profile->is_available ?? false) ? 'Available' : 'Offline' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Metrics & Dashboards --}}
        <div>
            {{-- Metrics Grid --}}
            <div class="kpi-cards-row">
                {{-- Total Orders --}}
                <div class="kpi-card" style="animation-delay:.05s">
                    <div>
                        <div class="kpi-label">Total Assigned</div>
                        <div class="kpi-val">{{ number_format($totalOrders) }}</div>
                        <div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;">Lifetime Orders</div>
                    </div>
                    <div style="color:var(--info);opacity:.75;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>

                {{-- Success Rate --}}
                <div class="kpi-card" style="animation-delay:.1s">
                    <div style="flex:1;">
                        <div class="kpi-label">Success Rate</div>
                        <div class="kpi-val" style="color:#4ade80;">{{ $successRate }}%</div>
                        <div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;">{{ number_format($delivered) }} delivered</div>
                    </div>
                    <div class="kpi-ring">
                        <svg viewBox="0 0 36 36" width="100%" height="100%">
                            <path stroke="rgba(255,255,255,.05)" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path stroke="#22c55e" stroke-width="3.5" stroke-dasharray="{{ min($successRate, 100) }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                    </div>
                </div>

                {{-- Average Rating --}}
                @php $ratingPct = ($avgRating / 5) * 100; @endphp
                <div class="kpi-card" style="animation-delay:.15s">
                    <div style="flex:1;">
                        <div class="kpi-label">Avg Rating</div>
                        <div class="kpi-val" style="color:#fbbf24;display:flex;align-items:center;gap:6px;">
                            {{ $avgRating }} <span style="font-size:1.3rem;">★</span>
                        </div>
                        <div style="font-size:.7rem;color:var(--text-dim);margin-top:4px;">{{ number_format($totalRatings) }} review{{ $totalRatings !== 1 ? 's' : '' }}</div>
                    </div>
                    <div class="kpi-ring">
                        <svg viewBox="0 0 36 36" width="100%" height="100%">
                            <path stroke="rgba(255,255,255,.05)" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path stroke="#fbbf24" stroke-width="3.5" stroke-dasharray="{{ round($ratingPct, 1) }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Graph and Status Split --}}
            <div class="dashboard-grid">
                {{-- SVG Trend Chart --}}
                <div class="chart-panel" style="animation: fu .45s .15s both;">
                    <div class="panel-heading">
                        <span>30-Day Orders Volume Trend</span>
                        <span style="font-size:.7rem;color:var(--text-dim);font-weight:500;">Last 30 Days</span>
                    </div>

                    <div style="flex: 1; min-height: 200px; display: flex; align-items: flex-end; position: relative; padding-left: 28px; padding-top: 10px;">
                        @php
                            $maxVal  = max(max($monthlyTrend->pluck('count')->toArray()), 1);
                            $svgW    = 600;
                            $svgH    = 180;
                            $padTop  = 20;
                            $cnt     = $monthlyTrend->count();
                            $points  = [];

                            foreach ($monthlyTrend as $idx => $stat) {
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
                                <linearGradient id="areaGradDriver" x1="0" y1="0" x2="0" y2="1">
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
                            <path d="{{ $areaD }}" fill="url(#areaGradDriver)"/>

                            {{-- Trend line --}}
                            <polyline fill="none" stroke="var(--red-lt)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" points="{{ $lineStr }}"/>

                            {{-- Data points + labels --}}
                            @foreach($monthlyTrend as $idx => $stat)
                                @php
                                    [$cx, $cy] = explode(',', $points[$idx]);
                                @endphp
                                @if($stat->count > 0)
                                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3.5" fill="var(--bg)" stroke="var(--red-lt)" stroke-width="1.8"/>
                                @endif
                                
                                {{-- Render labels occasionally to prevent overlap --}}
                                @if($idx % 6 === 0 || $idx === $cnt - 1)
                                    <text x="{{ $cx }}" y="{{ $svgH + 16 }}" font-size="8" fill="var(--text-dim)" text-anchor="middle">{{ date('d M', strtotime($stat->date)) }}</text>
                                @endif
                            @endforeach
                        </svg>
                    </div>
                </div>

                {{-- Status Breakdown --}}
                <div class="chart-panel" style="animation: fu .45s .2s both;">
                    <div class="panel-heading">Order Statuses</div>
                    <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">
                        @php
                            $statuses = [
                                'delivered' => ['label' => 'Delivered', 'color' => '#4ade80'],
                                'pending' => ['label' => 'Pending', 'color' => '#fbbf24'],
                                'picked_up' => ['label' => 'In Transit', 'color' => '#60a5fa'],
                                'rejected' => ['label' => 'Rejected', 'color' => '#f87171'],
                                'returned' => ['label' => 'Returned', 'color' => '#fb923c'],
                                'cancelled' => ['label' => 'Cancelled', 'color' => '#94a3b8'],
                            ];
                            $divisor = max($totalOrders, 1);
                        @endphp

                        @foreach($statuses as $key => $meta)
                            @php
                                $count = $statusCounts[$key] ?? 0;
                                $pct = round(($count / $divisor) * 100, 1);
                            @endphp
                            <div>
                                <div class="status-row">
                                    <span style="font-weight:500;color:var(--text-sub);">{{ $meta['label'] }}</span>
                                    <strong style="color:var(--text);">{{ number_format($count) }} <span style="font-size:.7rem;color:var(--text-dim);font-weight:normal;margin-left:4px;">({{ $pct }}%)</span></strong>
                                </div>
                                <div class="prog-wrap" style="margin-top:4px;">
                                    <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $meta['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Ratings --}}
    <div class="ratings-list-card" style="animation: fu .45s .25s both;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;">
            <span class="panel-title" style="font-size:.82rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.08em;">Recent Customer Reviews</span>
            <span style="font-size:.72rem;color:var(--text-dim);">Showing last 15 reviews</span>
        </div>

        @if($recentRatings->isEmpty())
            <div class="empty-state">
                <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <h3>No Customer Reviews</h3>
                <p>This driver has not received any ratings yet.</p>
            </div>
        @else
            <div>
                @foreach($recentRatings as $r)
                    <div class="review-item">
                        <div class="review-hd">
                            <div>
                                <span class="review-stars">
                                    @for($i=1;$i<=5;$i++)
                                        {{ $i <= $r->rating ? '★' : '☆' }}
                                    @endfor
                                </span>
                                @if($r->order)
                                    <span style="margin-left:10px;font-size:.78rem;color:var(--text-dim);">
                                        Order #<a href="{{ route('admin.orders.show', $r->order) }}" style="color:var(--red-lt);text-decoration:none;font-weight:600;">{{ $r->order->order_number }}</a>
                                    </span>
                                @endif
                            </div>
                            <span style="font-size:.75rem;color:var(--text-dim);">{{ $r->created_at->diffForHumans() }}</span>
                        </div>
                        @if($r->comment)
                            <div class="review-comment">"{{ $r->comment }}"</div>
                        @else
                            <div class="review-comment" style="color:var(--text-dim);opacity:.7;">No review text comment provided.</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
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
