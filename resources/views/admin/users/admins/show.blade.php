@extends('admin.layouts.app')

@section('title', $admin->name)

@section('page-title', 'Admin Profile')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.admins.index') }}">Admins</a>
    <span>/</span>
    <span>{{ $admin->name }}</span>
@endsection

@section('content')
<div class="profile-hd">
    <div class="profile-avatar">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
    <div class="profile-meta">
        <h2 class="profile-name">{{ $admin->name }}</h2>
        <div class="profile-sub">{{ $admin->email }}</div>
        <div class="profile-badges">
            @if($admin->role === 'superadmin') <span class="badge-superadmin">Superadmin</span>
            @else <span class="badge-admin">Admin</span>
            @endif
            @if($admin->status === 'active')     <span class="badge-active">Active</span>
            @elseif($admin->status === 'suspended') <span class="badge-suspended">Suspended</span>
            @else <span class="badge-pending">Pending</span>
            @endif
            @if($admin->adminProfile?->department)
                <span class="badge-info">{{ $admin->adminProfile->department }}</span>
            @endif
        </div>
    </div>
    @if($admin->id !== auth()->id())
    <div class="profile-actions">
        <a href="{{ route('admin.admins.edit', $admin) }}" class="btn-primary">Edit Admin</a>
        <button class="btn-danger"
            onclick="confirmDelete('{{ route('admin.admins.destroy', $admin) }}','{{ addslashes($admin->name) }}')">
            Delete
        </button>
    </div>
    @endif
</div>

<div class="info-grid">
    {{-- Contact --}}
    <div class="info-card">
        <div class="info-card-title">Account</div>
        <div class="info-rows">
            <div class="info-row"><span>Phone</span><strong>{{ $admin->phone ?? '—' }}</strong></div>
            <div class="info-row"><span>Department</span><strong>{{ $admin->adminProfile?->department ?: '—' }}</strong></div>
            <div class="info-row"><span>Joined</span><strong>{{ $admin->created_at->format('d M Y') }}</strong></div>
            <div class="info-row"><span>Last Updated</span><strong>{{ $admin->updated_at->format('d M Y') }}</strong></div>
        </div>
    </div>
    @if($admin->adminProfile?->notes)
    <div class="info-card">
        <div class="info-card-title">Notes</div>
        <p style="font-size:.84rem; color:var(--text-sub); line-height:1.6;">{{ $admin->adminProfile->notes }}</p>
    </div>
    @endif
</div>

{{-- Permissions --}}
@if($admin->role !== 'superadmin')
<div class="section-title" style="margin: 28px 0 12px; font-size: .78rem; font-weight: 700; color: var(--text-sub); letter-spacing: .1em; text-transform: uppercase;">
    Granted Permissions
</div>
@if($permissions->count())
<div class="perm-groups">
    @foreach($permissions as $group => $perms)
    <div>
        <div class="perm-group-title">{{ ucwords(str_replace('_', ' ', $group)) }}</div>
        <div class="perm-grid">
            @foreach($perms as $perm)
            <div class="perm-item granted">
                <svg width="14" height="14" fill="none" stroke="var(--success)" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <div>
                    <div class="perm-name">{{ $perm->display_name }}</div>
                    @if($perm->expires_at)
                        <div class="perm-desc">Expires {{ \Carbon\Carbon::parse($perm->expires_at)->format('d M Y') }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state" style="padding: 24px; margin: 0;">
    <p>No permissions granted yet. <a href="{{ route('admin.admins.edit', $admin) }}">Edit to add permissions.</a></p>
</div>
@endif
@else
<div style="padding: 20px; background: rgba(220,38,38,.08); border: 1px solid var(--bdr-red); border-radius: 10px; margin-top: 20px;">
    <strong style="color: var(--red-lt);">Superadmin</strong>
    <p style="color: var(--text-sub); font-size: .83rem; margin-top: 6px;">Superadmins have full access to all features and bypass permission checks.</p>
</div>
@endif
@endsection
