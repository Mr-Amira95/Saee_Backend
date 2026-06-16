@extends('admin.layouts.app')

@section('title', 'Clients')

@section('page-title', 'Clients')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Clients</span>
@endsection

@section('content')
{{-- Stats --}}
<div class="mini-stats">
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::count() }}</div>
        <div class="ms-lbl">Total Clients</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::where('status','active')->count() }}</div>
        <div class="ms-lbl">Active</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::where('status','pending_verification')->count() }}</div>
        <div class="ms-lbl">Pending</div>
    </div>
    <div class="mini-stat">
        <div class="ms-val">{{ \App\Models\ClientProfile::where('status','suspended')->count() }}</div>
        <div class="ms-lbl">Suspended</div>
    </div>
</div>

{{-- Filter bar --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.clients.index') }}" class="filter-form">
        <input
            class="filter-search"
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search company, email…"
        >
        <select class="filter-select" name="status">
            <option value="">All Statuses</option>
            <option value="active"               {{ request('status') === 'active'               ? 'selected' : '' }}>Active</option>
            <option value="pending_verification" {{ request('status') === 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
            <option value="suspended"            {{ request('status') === 'suspended'            ? 'selected' : '' }}>Suspended</option>
        </select>
        <button class="btn-secondary" type="submit">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.clients.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.clients.create') }}" class="btn-primary">+ Add Client</a>
</div>

{{-- Table --}}
@if($q->count())
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Company</th>
                <th>Master Account</th>
                <th>Phone</th>
                <th>City</th>
                <th>Credit Limit</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($q as $client)
            <tr>
                <td>
                    <div class="cell-name">
                        <div class="cell-avatar">{{ strtoupper(substr($client->company_name, 0, 2)) }}</div>
                        <div>
                            <div class="cell-main">{{ $client->company_name }}</div>
                            @if($client->company_name_ar)
                                <div class="cell-sub">{{ $client->company_name_ar }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <div class="cell-main">{{ $client->masterUser->name ?? '—' }}</div>
                    <div class="cell-sub">{{ $client->masterUser->email ?? '' }}</div>
                </td>
                <td>{{ $client->phone ?: '—' }}</td>
                <td>{{ $client->city ? $client->city->name : '—' }}</td>
                <td>{{ number_format($client->credit_limit, 2) }} JD</td>
                <td>
                    @if($client->status === 'active')       <span class="badge-active">Active</span>
                    @elseif($client->status === 'suspended') <span class="badge-suspended">Suspended</span>
                    @else                                    <span class="badge-pv">Pending</span>
                    @endif
                </td>
                <td>{{ $client->created_at->format('d M Y') }}</td>
                <td>
                    <div class="act-btns">
                        <a href="{{ route('admin.clients.show', $client) }}" class="act-btn act-view" title="View">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('admin.clients.edit', $client) }}" class="act-btn act-edit" title="Edit">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button
                            class="act-btn act-delete"
                            title="Delete"
                            onclick="confirmDelete('{{ route('admin.clients.destroy', $client) }}','{{ addslashes($client->company_name) }}')"
                        >
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <p>No clients found. <a href="{{ route('admin.clients.create') }}">Add the first client.</a></p>
</div>
@endif
@endsection
