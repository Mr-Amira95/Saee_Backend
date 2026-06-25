@extends('admin.layouts.app')

@section('title', $driver->user->name ?? 'Driver')
@section('page-title', 'Driver Profile')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.index') }}">Drivers</a>
    <span>/</span>
    <span>{{ $driver->user->name ?? '—' }}</span>
@endsection

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
.expiry-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 100px; font-size: .72rem; font-weight: 600;
}
.expiry-ok      { background: rgba(34,197,94,.12); color: #4ade80; }
.expiry-soon    { background: rgba(234,179,8,.12);  color: #fbbf24; }
.expiry-expired { background: rgba(220,38,38,.15);  color: #f87171; }

.file-link {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; background: rgba(220,38,38,.08);
    border: 1px solid rgba(220,38,38,.18); border-radius: 6px;
    color: var(--red); font-size: .78rem; font-weight: 600;
    text-decoration: none; transition: background .15s;
}
.file-link:hover { background: rgba(220,38,38,.16); }
</style>
@endsection

@section('content')

@php
    /* ── Licence expiry helpers ── */
    $licExp  = $driver->license_expiry_date  ? \Carbon\Carbon::parse($driver->license_expiry_date)  : null;
    $carExp  = $driver->car_license_expiry   ? \Carbon\Carbon::parse($driver->car_license_expiry)   : null;

    function expiryClass($exp): string {
        if (!$exp) return '';
        $d = now()->startOfDay()->diffInDays($exp, false);
        return $d < 0 ? 'expiry-expired' : ($d <= 30 ? 'expiry-soon' : 'expiry-ok');
    }
    function expiryLabel($exp): string {
        if (!$exp) return '—';
        $d = now()->startOfDay()->diffInDays($exp, false);
        return $d < 0 ? $exp->format('d M Y').' — EXPIRED'
                      : ($d === 0 ? $exp->format('d M Y').' (today)' : $exp->format('d M Y'));
    }
@endphp

{{-- ── Profile Header ── --}}
<div class="profile-hd">

    <div class="profile-avatar" style="background:linear-gradient(135deg,var(--red-dark),var(--red));">
        {{ strtoupper(substr($driver->user->name ?? '?', 0, 2)) }}
    </div>

    <div style="flex:1;min-width:180px;">
        <h2 class="profile-name">{{ $driver->user->name ?? '—' }}</h2>
        <div style="font-size:.85rem;color:var(--text-dim);margin-top:4px;">{{ $driver->user->email ?? '—' }}</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;align-items:center;">
            @if($driver->user?->status === 'active')        <span class="badge-active">Active</span>
            @elseif($driver->user?->status === 'suspended') <span class="badge-suspended">Suspended</span>
            @else                                           <span class="badge-pending">Pending</span>
            @endif

            @if($driver->is_available)
                <span class="badge-yes">Available</span>
            @else
                <span class="badge-no">Busy</span>
            @endif

            @if($licExp && $licExp->isPast())
                <span class="expiry-badge expiry-expired">⚠ License Expired</span>
            @endif
        </div>

        {{-- Row containing Shortcuts and Main Actions together --}}
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:14px; width:100%;">
            <a href="{{ route('admin.attendance.index', ['search' => $driver->user->name]) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                📅 Attendance History
            </a>
            <a href="{{ route('admin.drivers.location-history', $driver) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                📍 Location History
            </a>
            <a href="{{ route('admin.financials.settle-driver', $driver) }}" class="btn-secondary" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;">
                💳 Finances
            </a>
            <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn-primary" style="font-size:.78rem;padding:6px 12px;">Edit Driver</a>
            
            <form method="POST" action="{{ route('admin.drivers.toggle-status', $driver) }}" style="display:inline;">
                @csrf
                @method('PATCH')
                @if($driver->user?->status === 'active')
                    <button type="submit" class="btn-secondary" title="Deactivate Driver" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:4px;color:#fbbf24;border-color:rgba(234,179,8,.4);background:rgba(234,179,8,.1);">
                        ⏸ Deactivate
                    </button>
                @else
                    <button type="submit" class="btn-secondary" title="Activate Driver" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:4px;color:#4ade80;border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.1);">
                        ▶ Activate
                    </button>
                @endif
            </form>

            <form method="POST" action="{{ route('admin.drivers.resend-invitation', $driver) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-secondary" title="Resend invitation email" style="font-size:.78rem;padding:6px 12px;display:inline-flex;align-items:center;gap:4px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Resend Invitation
                </button>
            </form>
            <button class="btn-danger" style="font-size:.78rem;padding:6px 12px;"
                onclick="confirmDelete('{{ route('admin.drivers.destroy', $driver) }}','{{ addslashes($driver->user->name ?? 'this driver') }}')">
                Delete
            </button>
        </div>
    </div>
</div>

{{-- ── Info Grid ── --}}
<div class="info-grid">

    {{-- Contact --}}
    <div class="info-card">
        <div class="info-card-title">Contact</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">Email</span>
                <span class="info-row-val" style="word-break:break-all;">{{ $driver->user->email ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Phone</span>
                <span class="info-row-val">
                    @if($driver->user?->phone)
                        <span style="color:var(--text-dim);font-size:.8rem;margin-right:4px;">{{ $driver->user->phone_country_code ?? '' }}</span>{{ $driver->user->phone }}
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Account Status</span>
                <span class="info-row-val">
                    @if($driver->user?->status === 'active')        <span class="badge-active">Active</span>
                    @elseif($driver->user?->status === 'suspended') <span class="badge-suspended">Suspended</span>
                    @else <span class="badge-pending">Pending</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Availability</span>
                <span class="info-row-val">
                    @if($driver->is_available)
                        <span class="badge-yes">Available</span>
                    @else
                        <span class="badge-no">Busy / Unavailable</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Member Since</span>
                <span class="info-row-val">{{ $driver->user?->created_at?->format('d M Y') ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Identity & License --}}
    <div class="info-card">
        <div class="info-card-title">Identity &amp; License</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">National ID</span>
                <span class="info-row-val" style="font-family:monospace;letter-spacing:.03em;">{{ $driver->national_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">License No.</span>
                <span class="info-row-val" style="font-family:monospace;letter-spacing:.03em;">{{ $driver->license_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">License Expiry</span>
                <span class="info-row-val">
                    @if($licExp)
                        <span class="{{ expiryClass($licExp) ? 'expiry-badge '.expiryClass($licExp) : '' }}" style="{{ expiryClass($licExp) ? '' : 'color:var(--text);' }}">
                            {{ expiryLabel($licExp) }}
                        </span>
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">License File</span>
                <span class="info-row-val">
                    @if($driver->license_attachment)
                        <a href="{{ Storage::disk('public')->url($driver->license_attachment) }}" target="_blank" class="file-link">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"/></svg>
                            View File
                        </a>
                    @else
                        <span style="color:var(--text-dim);">—</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Vehicle --}}
    <div class="info-card">
        <div class="info-card-title">Vehicle</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">Vehicle Type</span>
                <span class="info-row-val">{{ $driver->vehicle_type ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Plate Number</span>
                <span class="info-row-val" style="font-family:monospace;letter-spacing:.05em;font-weight:700;">{{ $driver->vehicle_plate ?: '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Car License Expiry</span>
                <span class="info-row-val">
                    @if($carExp)
                        <span class="{{ expiryClass($carExp) ? 'expiry-badge '.expiryClass($carExp) : '' }}" style="{{ expiryClass($carExp) ? '' : 'color:var(--text);' }}">
                            {{ expiryLabel($carExp) }}
                        </span>
                    @else —
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Car License File</span>
                <span class="info-row-val">
                    @if($driver->car_license_attachment)
                        <a href="{{ Storage::disk('public')->url($driver->car_license_attachment) }}" target="_blank" class="file-link">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3"/></svg>
                            View File
                        </a>
                    @else
                        <span style="color:var(--text-dim);">—</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Salary Configuration --}}
    @php
        $salaryConfig = $driver->activeSalaryConfig;
        $perSalary    = $salaryConfig?->activePerSalaryConfig;
    @endphp
    <div class="info-card">
        <div class="info-card-title">Salary Configuration</div>
        @if(!$salaryConfig)
            <div style="padding:20px 0;text-align:center;">
                <div style="font-size:.78rem;color:var(--text-dim);">No salary configured for this driver.</div>
                <a href="{{ route('admin.drivers.edit', $driver) }}" style="font-size:.78rem;color:var(--red);margin-top:6px;display:inline-block;">Configure now →</a>
            </div>
        @else
            <div class="info-rows">
                <div class="info-row">
                    <span class="info-row-key">Salary Type</span>
                    <span class="info-row-val">
                        @if($salaryConfig->salary_type->value === 'per_salary')
                            <span class="badge-info">Per Salary</span>
                        @else
                            <span class="badge-info">Per Order</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-row-key">Active Since</span>
                    <span class="info-row-val">{{ $salaryConfig->effective_from->format('d M Y') }}</span>
                </div>

                @if($salaryConfig->salary_type->value === 'per_salary' && $perSalary)
                    <div class="info-row">
                        <span class="info-row-key">Basic Salary</span>
                        <span class="info-row-val" style="font-weight:600;">{{ number_format($perSalary->basic_salary, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Car &amp; Gasoline Allowance</span>
                        <span class="info-row-val" style="font-weight:600;">{{ number_format($perSalary->car_allowance, 2) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Order Bonus Threshold</span>
                        <span class="info-row-val">{{ $perSalary->extra_order_threshold }} orders / period</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-key">Bonus Per Extra Order</span>
                        <span class="info-row-val" style="font-weight:600;">{{ number_format($perSalary->extra_order_bonus, 2) }}</span>
                    </div>
                @elseif($salaryConfig->salary_type->value === 'per_order')
                    <div class="info-row">
                        <span class="info-row-key">Rate Source</span>
                        <span class="info-row-val" style="color:var(--text-dim);font-size:.82rem;">Global city rates apply</span>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Last Known Location --}}
    <div class="info-card" style="grid-column: span 2;">
        <div class="info-card-title">Last Known Location</div>
        <div class="info-rows" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="info-row" style="border-bottom: none; padding-bottom: 0;">
                <span class="info-row-key">Latitude</span>
                <span class="info-row-val" style="font-family:monospace;">{{ $driver->current_latitude ?? '—' }}</span>
            </div>
            <div class="info-row" style="border-bottom: none; padding-bottom: 0;">
                <span class="info-row-key">Longitude</span>
                <span class="info-row-val" style="font-family:monospace;">{{ $driver->current_longitude ?? '—' }}</span>
            </div>
            <div class="info-row" style="border-bottom: none; padding-bottom: 0;">
                <span class="info-row-key">Last Updated</span>
                <span class="info-row-val">
                    @if($driver->location_updated_at)
                        {{ \Carbon\Carbon::parse($driver->location_updated_at)->format('d M Y, H:i') }}
                        <span style="color:var(--text-dim);font-size:.78rem;display:block;margin-top:2px;">
                            {{ \Carbon\Carbon::parse($driver->location_updated_at)->diffForHumans() }}
                        </span>
                    @else —
                    @endif
                </span>
            </div>
        </div>
        @if($driver->current_latitude && $driver->current_longitude)
            <div id="map" style="width: 100%; height: 350px; border-radius: 12px; border: 1px solid var(--bdr); margin-top: 20px; background: #0c1230; z-index: 0;"></div>
        @else
            <div style="margin-top:16px;padding:14px;background:var(--in-bg);border-radius:10px;text-align:center;">
                <div style="font-size:.78rem;color:var(--text-dim);">No location data available yet.</div>
            </div>
        @endif
    </div>

</div>

{{-- Performance & Rating Section --}}
<div class="page-hd" style="margin-top: 28px; margin-bottom: 15px;">
    <div class="page-hd-left">
        <h2>Driver Performance &amp; Reviews</h2>
        <p>Real-time KPIs, attendance history, and customer reviews.</p>
    </div>
</div>

<div class="info-grid" style="margin-bottom: 24px;">
    {{-- Performance KPI Scorecard --}}
    <div class="info-card">
        <div class="info-card-title">Performance KPI Scorecard</div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-row-key">Average Rating</span>
                <span class="info-row-val" style="font-weight: 700; color: #fbbf24; font-size: 1.05rem; display: flex; align-items: center; gap: 6px;">
                    {{ $driver->user->average_rating }} ★
                    <span style="font-size: .78rem; color: var(--text-dim); font-weight: 400;">
                        ({{ $driver->user->driverRatings()->count() }} reviews)
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Delivery Success</span>
                <span class="info-row-val" style="font-weight: 700; color: #86efac; font-size: 1.05rem;">
                    {{ $driver->user->delivery_success_rate }}%
                    <span style="font-size: .76rem; color: var(--text-dim); font-weight: 400; display: block; margin-top: 2px;">
                        ({{ $driver->user->driverOrders()->where('status', 'delivered')->count() }} delivered / {{ $driver->user->driverOrders()->count() }} total)
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">Avg Transit Duration</span>
                <span class="info-row-val" style="font-weight: 700; color: #60a5fa; font-size: 1.05rem;">
                    {{ $driver->user->average_transit_hours ? $driver->user->average_transit_hours . ' hours' : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-row-key">On-Time Attendance</span>
                <span class="info-row-val" style="font-weight: 700; color: #c084fc; font-size: 1.05rem;">
                    @php
                        $totalAttendance = $driver->user->attendances()->count();
                        $lateAttendance = $driver->user->attendances()->whereRaw("TIME(check_in_at) > '09:15:00'")->count();
                        $onTimeRate = $totalAttendance > 0 ? round((($totalAttendance - $lateAttendance) / $totalAttendance) * 100) : 100;
                    @endphp
                    {{ $onTimeRate }}%
                    <span style="font-size: .76rem; color: var(--text-dim); font-weight: 400; display: block; margin-top: 2px;">
                        ({{ $totalAttendance - $lateAttendance }} on-time / {{ $totalAttendance }} total present)
                    </span>
                </span>
            </div>
        </div>
    </div>

    {{-- Recent Customer Reviews --}}
    <div class="info-card">
        <div class="info-card-title">Recent Customer Reviews</div>
        @php
            $reviews = $driver->user->driverRatings()->with('order')->latest()->take(3)->get();
        @endphp
        @forelse($reviews as $rev)
            <div style="background: rgba(255,255,255,.015); border: 1px solid var(--bdr); border-radius: 10px; padding: 12px; margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div style="color: #fbbf24; font-weight: 700; font-size: .85rem;">
                        @for($i = 1; $i <= 5; $i++)
                            {!! $i <= $rev->rating ? '&#9733;' : '&#9734;' !!}
                        @endfor
                    </div>
                    <a href="{{ route('admin.orders.show', $rev->order) }}" style="font-size: .74rem; color: var(--red-lt); font-weight: 600; text-decoration: none;">
                        #{{ $rev->order->order_number }}
                    </a>
                </div>
                <p style="font-size: .82rem; color: var(--text-sub); line-height: 1.4;">
                    {{ $rev->comment ?: 'No written comment left.' }}
                </p>
                <div style="font-size: .7rem; color: var(--text-dim); margin-top: 6px; text-align: right;">
                    {{ $rev->created_at->diffForHumans() }}
                </div>
            </div>
        @empty
            <div style="padding: 20px 0; text-align: center; color: var(--text-dim); font-size: .8rem;">
                No customer reviews submitted yet.
            </div>
        @endforelse
    </div>

    {{-- Attendance History Tab --}}
    <div class="info-card" style="grid-column: span 2;">
        <div class="info-card-title">Recent Attendance Logs</div>
        @php
            $recentLogs = $driver->user->attendances()->latest()->take(5)->get();
        @endphp
        <div class="table-wrap">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid var(--bdr);">
                        <th style="padding: 8px; font-size: .7rem; text-transform: uppercase; color: var(--text-dim);">Date</th>
                        <th style="padding: 8px; font-size: .7rem; text-transform: uppercase; color: var(--text-dim);">Check In</th>
                        <th style="padding: 8px; font-size: .7rem; text-transform: uppercase; color: var(--text-dim);">Check In Location</th>
                        <th style="padding: 8px; font-size: .7rem; text-transform: uppercase; color: var(--text-dim);">Check Out</th>
                        <th style="padding: 8px; font-size: .7rem; text-transform: uppercase; color: var(--text-dim);">Check Out Location</th>
                        <th style="padding: 8px; font-size: .7rem; text-transform: uppercase; color: var(--text-dim);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $l)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,.02);">
                            <td style="padding: 10px 8px; font-size: .8rem; font-weight: 600;">{{ $l->date->format('Y-m-d') }}</td>
                            <td style="padding: 10px 8px; font-size: .8rem; color: #86efac;">{{ $l->check_in_at->format('H:i:s') }}</td>
                            <td style="padding: 10px 8px; font-size: .78rem;">
                                @if($l->check_in_location)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $l->check_in_location }}" target="_blank" style="color: var(--red-lt); text-decoration: none;">
                                        {{ $l->check_in_location }}
                                    </a>
                                @else — @endif
                            </td>
                            <td style="padding: 10px 8px; font-size: .8rem; color: #fca5a5;">{{ $l->check_out_at ? $l->check_out_at->format('H:i:s') : '—' }}</td>
                            <td style="padding: 10px 8px; font-size: .78rem;">
                                @if($l->check_out_location)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $l->check_out_location }}" target="_blank" style="color: var(--red-lt); text-decoration: none;">
                                        {{ $l->check_out_location }}
                                    </a>
                                @else — @endif
                            </td>
                            <td style="padding: 10px 8px;">
                                @if($l->check_in_at->format('H:i:s') > '09:15:00')
                                    <span class="badge badge-suspended" style="font-size: .68rem;"><span class="badge-dot"></span> Late Check-In</span>
                                @else
                                    <span class="badge badge-active" style="font-size: .68rem;"><span class="badge-dot"></span> On Time</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 20px 8px; text-align: center; color: var(--text-dim); font-size: .8rem;">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var lat = {{ $driver->current_latitude ?? 'null' }};
        var lng = {{ $driver->current_longitude ?? 'null' }};
        
        if (lat !== null && lng !== null) {
            var map = L.map('map', { zoomControl: true }).setView([lat, lng], 14);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            var initials = "{{ strtoupper(substr($driver->user->name ?? '?', 0, 2)) }}";
            var driverIcon = L.divIcon({
                className: '',
                html: '<div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7f1d1d,#dc2626);border:2px solid #f87171;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.5);">' + initials + '</div>',
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -20]
            });

            L.marker([lat, lng], { icon: driverIcon }).addTo(map)
                .bindPopup("<b>{{ $driver->user->name ?? 'Driver' }}</b><br>Last known location.");
        }
    });
</script>
@endsection
