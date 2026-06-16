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
    .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media(max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }

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
                ->first();
        @endphp
        <div class="attendance-widget" style="background: rgba(255,255,255,.03); border: 1px solid var(--bdr); border-radius: 12px; padding: 12px 18px; display: flex; align-items: center; gap: 15px; min-width: 280px; backdrop-filter: blur(8px);">
            <div style="flex: 1; text-align: left;">
                <div style="font-size: .68rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em;">Shift Logs</div>
                <div id="attendanceStatus" style="font-size: .84rem; font-weight: 600; margin-top: 3px; color: var(--text);">
                    @if(!$todayAttendance)
                        Not Checked In
                    @elseif(!$todayAttendance->check_out_at)
                        Working since {{ $todayAttendance->check_in_at->format('H:i') }}
                    @else
                        Completed ({{ $todayAttendance->check_in_at->format('H:i') }} - {{ $todayAttendance->check_out_at->format('H:i') }})
                    @endif
                </div>
                @if($todayAttendance && !$todayAttendance->check_out_at)
                    <div id="activeTimer" style="font-size: .74rem; color: var(--red-lt); font-family: monospace; font-weight: 600; margin-top: 2px;" data-start="{{ $todayAttendance->check_in_at->toIso8601String() }}">00:00:00</div>
                @endif
            </div>
            
            <div style="display: flex; gap: 8px;">
                @if(!$todayAttendance)
                    <button class="btn-primary" id="dashboardCheckInBtn" onclick="submitAttendance('check-in')" style="padding: 8px 14px; font-size: .8rem; box-shadow: none;">Check In</button>
                @elseif(!$todayAttendance->check_out_at)
                    <button class="btn-danger" id="dashboardCheckOutBtn" onclick="submitAttendance('check-out')" style="padding: 8px 14px; font-size: .8rem;">Check Out</button>
                @else
                    <span class="badge badge-active" style="padding: 6px 12px; font-size: .76rem;"><span class="badge-dot"></span> Done</span>
                @endif
            </div>
        </div>
    @endif

    <div class="welcome-date" style="text-align: right; min-width: 120px;">{{ now()->format('D, d M Y') }}</div>
</div>

{{-- Metric cards --}}
<div class="cards-grid">
    <div class="metric" style="--m-color:#ef4444;--m-icon-bg:rgba(220,38,38,.12);animation-delay:.1s">
        <div class="metric-head">
            <div class="metric-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <span class="metric-trend">↑ 12%</span>
        </div>
        <div class="metric-val">{{ \App\Models\DriverProfile::whereHas('user', fn($q) => $q->where('status','active'))->count() }}</div>
        <div class="metric-lbl">Active Drivers</div>
    </div>

    <div class="metric" style="--m-color:#3b82f6;--m-icon-bg:rgba(59,130,246,.12);animation-delay:.15s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#60a5fa">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="metric-trend">↑ 8%</span>
        </div>
        <div class="metric-val">{{ \App\Models\ClientProfile::where('status','active')->count() }}</div>
        <div class="metric-lbl">Client Companies</div>
    </div>

    <div class="metric" style="--m-color:#a855f7;--m-icon-bg:rgba(168,85,247,.12);animation-delay:.2s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#c084fc">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="metric-trend">↑ 24%</span>
        </div>
        <div class="metric-val">{{ \App\Models\Order::count() }}</div>
        <div class="metric-lbl">Total Orders</div>
    </div>

    <div class="metric" style="--m-color:#10b981;--m-icon-bg:rgba(16,185,129,.12);animation-delay:.25s">
        <div class="metric-head">
            <div class="metric-icon" style="color:#34d399">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="metric-trend">↑ 3%</span>
        </div>
        <div class="metric-val">{{ number_format(\App\Models\Order::where('status', 'delivered')->sum('delivery_amount'), 2) }}</div>
        <div class="metric-lbl">Revenue (JD)</div>
    </div>
</div>

{{-- Bottom two-column --}}
<div class="bottom-grid">
    {{-- Recent activity --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Recent Activity</span>
            <a href="#" class="panel-link">View all →</a>
        </div>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-dot" style="background:#ef4444"></div>
                <div class="activity-body">
                    <div class="activity-msg">System <strong>initialized</strong> — database migrations completed.</div>
                    <div class="activity-time">Just now</div>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-dot" style="background:#3b82f6"></div>
                <div class="activity-body">
                    <div class="activity-msg"><strong>Superadmin</strong> account created and seeded.</div>
                    <div class="activity-time">{{ now()->diffForHumans() }}</div>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-dot" style="background:#a855f7"></div>
                <div class="activity-body">
                    <div class="activity-msg">Permission system configured — <strong>33 permissions</strong> seeded.</div>
                    <div class="activity-time">{{ now()->diffForHumans() }}</div>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-dot" style="background:#10b981"></div>
                <div class="activity-body">
                    <div class="activity-msg">Sanctum API authentication <strong>activated</strong>.</div>
                    <div class="activity-time">{{ now()->diffForHumans() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- System status --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">System Status</span>
            <span style="font-size:.72rem;color:#4ade80;font-weight:600">● All Systems Operational</span>
        </div>
        <div class="quick-stats">
            <div class="qs-item">
                <div class="qs-left">
                    <div class="qs-icon" style="background:rgba(220,38,38,.1);color:#ef4444">💻</div>
                    <div>
                        <div class="qs-label">Application Server</div>
                        <div class="qs-bar-wrap"><div class="qs-bar" style="background:#dc2626" data-target="100"></div></div>
                    </div>
                </div>
                <div class="qs-val" style="color:#4ade80">Online</div>
            </div>
            <div class="qs-item">
                <div class="qs-left">
                    <div class="qs-icon" style="background:rgba(59,130,246,.1);color:#60a5fa">🌐</div>
                    <div>
                        <div class="qs-label">Logistics APIs</div>
                        <div class="qs-bar-wrap"><div class="qs-bar" style="background:#3b82f6" data-target="100"></div></div>
                    </div>
                </div>
                <div class="qs-val" style="color:#4ade80">99.9% Uptime</div>
            </div>
            <div class="qs-item">
                <div class="qs-left">
                    <div class="qs-icon" style="background:rgba(16,185,129,.1);color:#34d399">🔑</div>
                    <div>
                        <div class="qs-label">Auth (Sanctum)</div>
                        <div class="qs-bar-wrap"><div class="qs-bar" style="background:#10b981" data-target="100"></div></div>
                    </div>
                </div>
                <div class="qs-val" style="color:#4ade80">Active</div>
            </div>
            <div class="qs-item">
                <div class="qs-left">
                    <div class="qs-icon" style="background:rgba(245,158,11,.1);color:#fcd34d">🗄️</div>
                    <div>
                        <div class="qs-label">Database (MySQL)</div>
                        <div class="qs-bar-wrap"><div class="qs-bar" style="background:#f59e0b" data-target="100"></div></div>
                    </div>
                </div>
                <div class="qs-val" style="color:#4ade80">Connected</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.qs-bar').forEach(bar => {
    const target = parseInt(bar.dataset.target) || 0;
    setTimeout(() => { bar.style.width = target + '%'; }, 400);
});

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
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Verification failed.');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(err => {
            alert('A network error occurred. Please try again.');
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
</script>
@endsection
