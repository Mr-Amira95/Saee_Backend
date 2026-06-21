@extends('admin.layouts.app')

@section('title', 'Drivers')

@section('page-title', 'Drivers')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Drivers</span>
@endsection

@section('content')
<div class="mini-stats">
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::count() }}</div>
        <div class="ms-lbl">Total Drivers</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::where('is_available', true)->count() }}</div>
        <div class="ms-lbl">Available</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::where('is_available', false)->count() }}</div>
        <div class="ms-lbl">Busy</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\DriverProfile::whereHas('user', fn($q) => $q->where('status','suspended'))->count() }}</div>
        <div class="ms-lbl">Suspended</div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('admin.drivers.index') }}" class="filter-form">
        <input class="filter-search" type="text" name="search" value="{{ request('search') }}" placeholder="Search name, plate, national ID…">
        <select class="filter-select" name="status">
            <option value="">All Statuses</option>
            <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <select class="filter-select" name="available">
            <option value="">Availability</option>
            <option value="1" {{ request('available') === '1' ? 'selected' : '' }}>Available</option>
            <option value="0" {{ request('available') === '0' ? 'selected' : '' }}>Busy</option>
        </select>
        <button class="btn-secondary" type="submit">Filter</button>
        @if(request('search') || request('status') || request('available') !== null && request('available') !== '')
            <a href="{{ route('admin.drivers.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.drivers.live-map') }}" class="btn-secondary">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Live Map
    </a>
    <a href="{{ route('admin.drivers.create') }}" class="btn-primary">+ Add Driver</a>
</div>

@if($q->count())
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>License</th>
                <th>License Expiry</th>
                <th>Available</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($q as $driver)
            <tr>
                <td>
                    <div class="cell-name">
                        <div class="cell-avatar">{{ strtoupper(substr($driver->user->name ?? '?', 0, 2)) }}</div>
                        <div>
                            <div class="cell-main">{{ $driver->user->name ?? '—' }}</div>
                            <div class="cell-sub">{{ $driver->user->email ?? '' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="cell-main">{{ $driver->vehicle_plate ?: '—' }}</div>
                    <div class="cell-sub">{{ trim(($driver->vehicle_model ?? '').' '.($driver->vehicle_color ?? '')) ?: '' }}</div>
                </td>
                <td>{{ $driver->license_number }}</td>
                <td>
                    @php
                        $expiry = \Carbon\Carbon::parse($driver->license_expiry_date);
                        $isExpired = $expiry->isPast();
                        $isSoon = !$isExpired && $expiry->diffInDays(now()) <= 30;
                    @endphp
                    <span style="color: {{ $isExpired ? 'var(--red-lt)' : ($isSoon ? 'var(--warning)' : 'inherit') }}">
                        {{ $expiry->format('d M Y') }}
                        @if($isExpired) <small>(Expired)</small>
                        @elseif($isSoon) <small>(Soon)</small>
                        @endif
                    </span>
                </td>
                <td>
                    @if($driver->is_available) <span class="badge-yes">Yes</span>
                    @else <span class="badge-no">No</span>
                    @endif
                </td>
                <td>
                    @if($driver->user?->status === 'active')     <span class="badge-active">Active</span>
                    @elseif($driver->user?->status === 'suspended') <span class="badge-suspended">Suspended</span>
                    @else <span class="badge-pending">Pending</span>
                    @endif
                </td>
                <td>
                    <div class="act-btns">
                        <a href="{{ route('admin.drivers.show', $driver) }}" class="act-btn act-view" title="View">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="act-btn act-edit" title="Edit">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button class="act-btn act-delete" title="Delete"
                            onclick="confirmDelete('{{ route('admin.drivers.destroy', $driver) }}','{{ addslashes($driver->user->name ?? 'this driver') }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $q->links() }}
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    <p>No drivers found. <a href="{{ route('admin.drivers.create') }}">Add the first driver.</a></p>
</div>
@endif
@endsection
