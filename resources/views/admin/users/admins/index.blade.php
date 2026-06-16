@extends('admin.layouts.app')

@section('title', 'Admins')

@section('page-title', 'Admins')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Admins</span>
@endsection

@section('content')
<div class="mini-stats">
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\User::where('role','admin')->count() }}</div>
        <div class="ms-lbl">Admins</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\User::where('role','superadmin')->count() }}</div>
        <div class="ms-lbl">Superadmins</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\User::whereIn('role',['admin','superadmin'])->where('status','active')->count() }}</div>
        <div class="ms-lbl">Active</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\User::whereIn('role',['admin','superadmin'])->where('status','suspended')->count() }}</div>
        <div class="ms-lbl">Suspended</div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('admin.admins.index') }}" class="filter-form">
        <input class="filter-search" type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…">
        <select class="filter-select" name="role">
            <option value="">All Roles</option>
            <option value="admin"      {{ request('role') === 'admin'      ? 'selected' : '' }}>Admin</option>
            <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
        </select>
        <select class="filter-select" name="status">
            <option value="">All Statuses</option>
            <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <button class="btn-secondary" type="submit">Filter</button>
        @if(request('search') || request('status') || request('role'))
            <a href="{{ route('admin.admins.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.admins.create') }}" class="btn-primary">+ Add Admin</a>
</div>

@if($q->count())
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Admin</th>
                <th>Department</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($q as $admin)
            <tr>
                <td>
                    <div class="cell-name">
                        <div class="cell-avatar">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
                        <div>
                            <div class="cell-main">{{ $admin->name }}</div>
                            <div class="cell-sub">{{ $admin->email }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $admin->adminProfile?->department ?: '—' }}</td>
                <td>
                    @if($admin->role === 'superadmin') <span class="badge-superadmin">Superadmin</span>
                    @else <span class="badge-admin">Admin</span>
                    @endif
                </td>
                <td>
                    @if($admin->status === 'active')     <span class="badge-active">Active</span>
                    @elseif($admin->status === 'suspended') <span class="badge-suspended">Suspended</span>
                    @else <span class="badge-pending">Pending</span>
                    @endif
                </td>
                <td>{{ $admin->created_at->format('d M Y') }}</td>
                <td>
                    <div class="act-btns">
                        <a href="{{ route('admin.admins.show', $admin) }}" class="act-btn act-view" title="View">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @if($admin->role !== 'superadmin' || auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.admins.edit', $admin) }}" class="act-btn act-edit" title="Edit">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @if($admin->id !== auth()->id())
                        <button class="act-btn act-delete" title="Delete"
                            onclick="confirmDelete('{{ route('admin.admins.destroy', $admin) }}','{{ addslashes($admin->name) }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endif
                        @endif
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    <p>No admins found. <a href="{{ route('admin.admins.create') }}">Add the first admin.</a></p>
</div>
@endif
@endsection
