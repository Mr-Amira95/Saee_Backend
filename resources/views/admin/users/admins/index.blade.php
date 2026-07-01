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

<div class="filter-bar" style="display:flex; justify-content:space-between; align-items:center; gap:16px;">
    <form method="GET" action="{{ route('admin.admins.index') }}" class="filter-form" style="margin:0; flex:1; max-width:320px;" id="search-form">
        <input
            class="filter-search"
            type="text"
            name="search"
            id="search-input"
            value="{{ request('search') }}"
            placeholder="Search name, email or phone…"
            style="width:100%;"
        >
    </form>
    <a href="{{ route('admin.admins.create') }}" class="btn-primary">+ Add Admin</a>
</div>

@if($q->count())
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($q as $admin)
            <tr>
                <td>
                    <div class="cell-name">
                        <div class="cell-avatar">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
                        <div class="cell-main">{{ $admin->name }}</div>
                    </div>
                </td>
                <td>{{ $admin->email }}</td>
                <td>
                    @if($admin->phone)
                        <span style="color:var(--text-dim);font-size:.8rem;margin-right:4px;">{{ $admin->phone_country_code ?? '' }}</span>{{ $admin->phone }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <div class="act-btns">
                        <form method="POST" action="{{ route('admin.admins.resend-invitation', $admin) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="act-btn act-resend" title="Resend Code" style="cursor:pointer;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </button>
                        </form>
                        <button class="act-btn act-reset-pw" title="Reset Password"
                            onclick="openResetPasswordModal('{{ route('admin.admins.reset-password', $admin) }}','{{ addslashes($admin->name) }}')">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
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

@section('scripts')
<script>
    // Real-time search script
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        var timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                document.getElementById('search-form').submit();
            }, 500);
        });
        // Keep focus at end of input
        searchInput.focus();
        var val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
    }
</script>
@endsection
