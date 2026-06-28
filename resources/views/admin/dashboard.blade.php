@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <span>/</span>
    <span>Overview</span>
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

    /* Activity list */
    .activity-list { padding: 8px 0; }
    .activity-item {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 11px 20px; transition: background .12s;
    }
    .activity-item:hover { background: rgba(255,255,255,.018); }
    .activity-dot  { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .activity-body { flex: 1; }
    .activity-msg  { font-size: .82rem; color: var(--text-sub); line-height: 1.45; }
    .activity-msg strong { color: var(--text); }
    .activity-time { font-size: .72rem; color: var(--text-dim); margin-top: 3px; }

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
</style>
@endsection

@section('content')
{{-- Welcome & Shift Log row --}}
<div class="welcome-row">
    <div class="welcome-text" style="flex: 1; min-width: 200px;">
        <h2>Good {{ now()->format('G') < 12 ? 'morning' : (now()->format('G') < 17 ? 'afternoon' : 'evening') }}, <em>{{ explode(' ', auth()->user()->name)[0] }}</em></h2>
        <p>Here's what's happening at Sa'ee Logistics today.</p>
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
            <div style="flex: 1; text-align: left;">
                <div style="font-size: .68rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em;">
                    Shift Logs
                    @if($todaySessionCount > 1)
                        <span style="color: var(--red-lt); margin-left: 4px;">({{ $todaySessionCount }} sessions)</span>
                    @endif
                </div>
                <div id="attendanceStatus" style="font-size: .84rem; font-weight: 600; margin-top: 3px; color: var(--text);">
                    @if(!$todayAttendance)
                        Not Checked In
                    @elseif(!$todayAttendance->check_out_at)
                        Working since {{ $todayAttendance->check_in_at->format('H:i') }}
                    @else
                        Last shift: {{ $todayAttendance->check_in_at->format('H:i') }} – {{ $todayAttendance->check_out_at->format('H:i') }}
                    @endif
                </div>
                @if($todayAttendance && !$todayAttendance->check_out_at)
                    <div id="activeTimer" style="font-size: .74rem; color: var(--red-lt); font-family: monospace; font-weight: 600; margin-top: 2px;" data-start="{{ $todayAttendance->check_in_at->toIso8601String() }}">00:00:00</div>
                @endif
            </div>

            <div style="display: flex; gap: 8px;">
                @if(!$todayAttendance || $todayAttendance->check_out_at)
                    <button class="btn-primary" id="dashboardCheckInBtn" onclick="submitAttendance('check-in')" style="padding: 8px 14px; font-size: .8rem; box-shadow: none;">Check In</button>
                @else
                    <button class="btn-danger" id="dashboardCheckOutBtn" onclick="submitAttendance('check-out')" style="padding: 8px 14px; font-size: .8rem;">Check Out</button>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Metric cards --}}
<div class="cards-grid">
    <div class="metric" style="--m-color:#ef4444;--m-icon-bg:rgba(220,38,38,.12);animation-delay:.1s">
        <div class="metric-head">
            <div class="metric-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <span class="metric-trend">↑ Active</span>
        </div>
        <div class="metric-val">{{ $activeDriversCount }}</div>
        <div class="metric-lbl">Active Drivers</div>
    </div>

    <div class="metric" style="--m-color:#3b82f6;--m-icon-bg:rgba(59,130,246,.12);animation-delay:.15s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#60a5fa">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="metric-trend">↑ Registered</span>
        </div>
        <div class="metric-val">{{ $activeClientsCount }}</div>
        <div class="metric-lbl">Client Companies</div>
    </div>

    <div class="metric" style="--m-color:#a855f7;--m-icon-bg:rgba(168,85,247,.12);animation-delay:.2s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#c084fc">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="metric-trend">Total</span>
        </div>
        <div class="metric-val">{{ $totalOrdersCount }}</div>
        <div class="metric-lbl">Total Orders</div>
    </div>

    <div class="metric" style="--m-color:#10b981;--m-icon-bg:rgba(16,185,129,.12);animation-delay:.25s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#34d399">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="metric-trend">Delivered</span>
        </div>
        <div class="metric-val">{{ number_format($totalRevenue, 2) }}</div>
        <div class="metric-lbl">Revenue (JD)</div>
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
        <span class="panel-title">Operational Order Status</span>
        <span style="font-size: .72rem; color: var(--text-dim); font-weight: 500;">Live distribution across states</span>
    </div>
    <div style="padding: 20px 24px;">
        <!-- Status Bar -->
        <div style="height: 10px; display: flex; border-radius: 5px; overflow: hidden; background: rgba(255,255,255,.04); margin-bottom: 20px;">
            <div style="width: {{ $getPercentage('pending') }}%; background: var(--warning);" title="Pending: {{ round($getPercentage('pending'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('picked_up') }}%; background: var(--info);" title="In Transit: {{ round($getPercentage('picked_up'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('delivered') }}%; background: var(--success);" title="Delivered: {{ round($getPercentage('delivered'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('rejected') + $getPercentage('returned') }}%; background: #f87171;" title="Failed/Returned: {{ round($getPercentage('rejected') + $getPercentage('returned'), 1) }}%"></div>
            <div style="width: {{ $getPercentage('cancelled') }}%; background: var(--text-dim);" title="Cancelled: {{ round($getPercentage('cancelled'), 1) }}%"></div>
        </div>
        <!-- Grid details -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px;">
            <div style="background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; text-align: center;">
                <span class="badge badge-pending" style="font-size:.65rem; padding: 2px 7px;"><span class="badge-dot"></span>Pending</span>
                <div style="font-size: 1.35rem; font-weight: 800; margin-top: 4px; color: var(--text);">{{ $statusCounts['pending'] ?? 0 }}</div>
            </div>
            <div style="background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; text-align: center;">
                <span class="badge badge-info" style="font-size:.65rem; padding: 2px 7px;"><span class="badge-dot"></span>In Transit</span>
                <div style="font-size: 1.35rem; font-weight: 800; margin-top: 4px; color: var(--text);">{{ $statusCounts['picked_up'] ?? 0 }}</div>
            </div>
            <div style="background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; text-align: center;">
                <span class="badge badge-success" style="font-size:.65rem; padding: 2px 7px;"><span class="badge-dot"></span>Delivered</span>
                <div style="font-size: 1.35rem; font-weight: 800; margin-top: 4px; color: var(--text);">{{ $statusCounts['delivered'] ?? 0 }}</div>
            </div>
            <div style="background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; text-align: center;">
                <span class="badge badge-danger" style="font-size:.65rem; padding: 2px 7px;"><span class="badge-dot"></span>Returned/Failed</span>
                <div style="font-size: 1.35rem; font-weight: 800; margin-top: 4px; color: var(--text);">{{ ($statusCounts['returned'] ?? 0) + ($statusCounts['rejected'] ?? 0) }}</div>
            </div>
            <div style="background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; text-align: center;">
                <span class="badge badge-neutral" style="font-size:.65rem; padding: 2px 7px;"><span class="badge-dot"></span>Cancelled</span>
                <div style="font-size: 1.35rem; font-weight: 800; margin-top: 4px; color: var(--text);">{{ $statusCounts['cancelled'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Bottom two-column --}}
<div class="bottom-grid">
    {{-- Recent activity --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Recent Activity</span>
            <span style="font-size: .72rem; color: var(--text-dim);">Real-time events</span>
        </div>
        <div class="activity-list">
            @forelse($recentActivities as $act)
            <div class="activity-item">
                <div class="activity-dot" style="background:{{ $act['dot_color'] }}"></div>
                <div class="activity-body">
                    <div class="activity-msg">{!! $act['message'] !!}</div>
                    <div class="activity-time">{{ $act['time']->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="padding: 30px; text-align: center; color: var(--text-dim); font-size: .84rem;">No recent activities today.</div>
            @endforelse
        </div>
    </div>

    {{-- Pending action center --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Pending Action Items</span>
            <span class="badge badge-pending" style="font-size:.72rem;">{{ $unassignedOrdersCount + $openTicketsCount }} unresolved</span>
        </div>
        <div class="quick-stats" style="padding: 0;">
            <!-- Unassigned Orders Alert -->
            <div style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--bdr); transition: background .12s;" onmouseenter="this.style.background='rgba(255,255,255,.01)';" onmouseleave="this.style.background='transparent';">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245,158,11,.1); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink:0;">📦</div>
                    <div>
                        <div style="font-size: .82rem; font-weight: 600; color: var(--text);">Unassigned Shipments</div>
                        <div style="font-size: .72rem; color: var(--text-dim);">{{ $unassignedOrdersCount }} orders waiting for courier assignments</div>
                    </div>
                </div>
                <div>
                    @if($unassignedOrdersCount > 0)
                        <a href="{{ route('admin.orders.index') }}" class="btn-primary" style="padding: 5px 12px; font-size: .72rem; box-shadow: none; border-radius: 6px;">Dispatch</a>
                    @else
                        <span style="font-size: .72rem; color: var(--success); font-weight: 600;">● Cleared</span>
                    @endif
                </div>
            </div>

            <!-- Open Support Tickets -->
            @forelse($openTickets as $ticket)
            <div style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--bdr); transition: background .12s;" onmouseenter="this.style.background='rgba(255,255,255,.01)';" onmouseleave="this.style.background='transparent';">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,.1); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink:0;">💬</div>
                    <div style="min-width: 0;">
                        <div style="font-size: .82rem; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Ticket #{{ $ticket->ticket_number }}: {{ $ticket->title }}</div>
                        <div style="font-size: .72rem; color: var(--text-dim); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            By {{ $ticket->user->name ?? 'Client' }} • {{ $ticket->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.support.index', ['ticket' => $ticket->ticket_number]) }}" class="btn-secondary" style="padding: 5px 12px; font-size: .72rem; border-radius: 6px;">Reply</a>
                </div>
            </div>
            @empty
            <div style="padding: 30px; text-align: center; color: var(--text-dim); font-size: .84rem;">No pending support tickets. All quiet!</div>
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
<script>

/* Post Attendance Check-In / Check-Out */
function submitAttendance(type) {
    const btn = type === 'check-in' ? document.getElementById('dashboardCheckInBtn') : document.getElementById('dashboardCheckOutBtn');
    const statusText = document.getElementById('attendanceStatus');
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Locating...';

    const sendAttendanceRequest = (coords = null) => {
        btn.textContent = 'Saving...';
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
                showToast('Success', data.message, true, () => {
                    window.location.reload();
                });
            } else {
                showToast('Verification Failed', data.message || 'Check-in failed.', false);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(err => {
            showToast('Error', 'A network error occurred. Please try again.', false);
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
