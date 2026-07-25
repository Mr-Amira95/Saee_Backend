@extends('admin.layouts.app')

@section('title', __('Attendance Logs'))
@section('page-title', __('Attendance Logs'))

@section('breadcrumb')
    <span class="sep">/</span> <span class="current">{{ __('Attendance Logs') }}</span>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 18px;">

    {{-- Filter Bar --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="filter-form">
            <input
                type="text"
                name="search"
                class="filter-search"
                placeholder="{{ __('Search employee or driver...') }}"
                value="{{ request('search') }}"
            >

            <input
                type="date"
                name="date"
                class="filter-select"
                style="padding-right: 12px; background-image: none;"
                value="{{ request('date') }}"
            >

            <select name="role" class="filter-select">
                <option value="">{{ __('All Roles') }}</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('Admin Employees') }}</option>
                <option value="driver" {{ request('role') === 'driver' ? 'selected' : '' }}>{{ __('Drivers') }}</option>
            </select>

            <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: .83rem; box-shadow: none;">
                {{ __('Apply Filters') }}
            </button>

            @if(request()->anyFilled(['search', 'date', 'role']))
                <a href="{{ route('admin.attendance.index') }}" class="btn-secondary" style="padding: 8px 16px; font-size: .83rem;">
                    {{ __('Clear') }}
                </a>
            @endif
        </form>
    </div>

    {{-- Logs Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Employee / Driver') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Check In') }}</th>
                        <th>{{ __('Check In Location') }}</th>
                        <th>{{ __('Check Out') }}</th>
                        <th>{{ __('Check Out Location') }}</th>
                        <th>{{ __('Duration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $log)
                        <tr>
                            <td>
                                <div class="cell-name">
                                    <div class="cell-avatar" style="width:28px; height:28px; border-radius:6px; font-size:.65rem;">
                                        {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="cell-main">
                                            @if($log->user->isDriver() && $log->user->driverProfile)
                                                <a href="{{ route('admin.drivers.show', $log->user->driverProfile) }}" style="color: inherit; text-decoration: none;">
                                                    {{ $log->user->name }}
                                                </a>
                                            @else
                                                {{ $log->user->name }}
                                            @endif
                                        </span>
                                        <div class="cell-sub" style="font-size: .7rem;">{{ $log->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $log->user->isDriver() ? 'badge-info' : 'badge-pv' }}">
                                    {{ $log->user->role }}
                                </span>
                            </td>
                            <td style="color: var(--text-sub); font-size: .8rem; font-weight: 500;">
                                {{ $log->date->format('Y-m-d') }}
                            </td>
                            <td style="font-weight: 600; color: #86efac; font-size: .82rem;">
                                {{ $log->check_in_at->format('H:i:s') }}
                            </td>
                            <td>
                                @if($log->check_in_location)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $log->check_in_location }}" target="_blank" style="color: var(--red-lt); font-size: .78rem; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $log->check_in_location }}
                                    </a>
                                @else
                                    <span style="color: var(--text-dim); font-size: .78rem;">—</span>
                                @endif
                            </td>
                            <td style="font-weight: 600; color: #fca5a5; font-size: .82rem;">
                                {{ $log->check_out_at ? $log->check_out_at->format('H:i:s') : '—' }}
                            </td>
                            <td>
                                @if($log->check_out_location)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $log->check_out_location }}" target="_blank" style="color: var(--red-lt); font-size: .78rem; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $log->check_out_location }}
                                    </a>
                                @else
                                    <span style="color: var(--text-dim); font-size: .78rem;">—</span>
                                @endif
                            </td>
                            <td style="color: var(--text-sub); font-size: .8rem; font-weight: 500; font-family: monospace;">
                                @if($log->check_out_at)
                                    @php
                                        $diff = $log->check_in_at->diff($log->check_out_at);
                                        $hours = str_pad($diff->h + ($diff->days * 24), 2, '0', STR_PAD_LEFT);
                                        $minutes = str_pad($diff->i, 2, '0', STR_PAD_LEFT);
                                        $seconds = str_pad($diff->s, 2, '0', STR_PAD_LEFT);
                                    @endphp
                                    {{ "{$hours}h {$minutes}m" }}
                                @else
                                    <span style="color: var(--red-lt); font-weight: 600; animation: dot-p 2.5s infinite;">● {{ __('Active') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h3>{{ __('No Attendance Logs Found') }}</h3>
                                    <p>{{ __('Check back later or adjust filters.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">
                    {{ __('Showing') }} {{ $attendances->firstItem() }} {{ __('to') }} {{ $attendances->lastItem() }} {{ __('of') }} {{ $attendances->total() }} {{ __('logs') }}
                </div>
                <div class="pag-links">
                    {{ $attendances->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
