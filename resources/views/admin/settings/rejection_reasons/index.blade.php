@extends('admin.layouts.app')

@section('title', 'Rejection Reasons')
@section('page-title', 'Rejection Reasons')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Rejection Reasons</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="mini-stats">
    <div class="mini-stat">
        <div>
            <div class="mini-stat-val">{{ \App\Models\RejectionReason::count() }}</div>
            <div class="mini-stat-lbl">Total Reasons</div>
        </div>
    </div>
    <div class="mini-stat">
        <div>
            <div class="mini-stat-val">{{ \App\Models\RejectionReason::where('is_active', true)->count() }}</div>
            <div class="mini-stat-lbl">Active</div>
        </div>
    </div>
    <div class="mini-stat">
        <div>
            <div class="mini-stat-val">{{ \App\Models\RejectionReason::where('is_active', false)->count() }}</div>
            <div class="mini-stat-lbl">Inactive</div>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('admin.rejection-reasons.index') }}" class="filter-form">
        <input class="filter-search" type="text" name="search"
               value="{{ request('search') }}" placeholder="Search reason…">
        <button class="btn-secondary" type="submit">Filter</button>
        @if(request('search'))
            <a href="{{ route('admin.rejection-reasons.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
    @if(auth()->user()->hasAdminAction('rejection_reasons.add'))
    <a href="{{ route('admin.rejection-reasons.create') }}" class="btn-primary">+ Add Reason</a>
    @endif
</div>

{{-- Table --}}
@if($reasons->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reason (EN)</th>
                    <th>Reason (AR)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reasons as $reason)
                <tr>
                    <td><span style="color:var(--text-dim);font-size:.82rem;">{{ $reason->id }}</span></td>
                    <td><div class="cell-main">{{ $reason->reason }}</div></td>
                    <td><div class="cell-sub" style="font-size:.88rem;color:var(--text);" dir="rtl">{{ $reason->reason_ar ?: '—' }}</div></td>
                    <td>
                        @if($reason->is_active)
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            {{-- Active / Inactive toggle --}}
                            @if(auth()->user()->hasAdminAction('rejection_reasons.activate'))
                            <form method="POST" action="{{ route('admin.rejection-reasons.toggle', $reason) }}" style="display:contents;">
                                @csrf
                                @method('PATCH')
                                <label class="toggle-switch" title="{{ $reason->is_active ? 'Deactivate' : 'Activate' }}">
                                    <input type="checkbox" onchange="this.form.submit()" {{ $reason->is_active ? 'checked' : '' }}>
                                    <span class="toggle-track"><span class="toggle-thumb"></span></span>
                                </label>
                            </form>
                            @endif
                            @if(auth()->user()->hasAdminAction('rejection_reasons.edit'))
                            <a href="{{ route('admin.rejection-reasons.edit', $reason) }}" class="act-btn act-edit" title="Edit">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                            @if(auth()->user()->hasAdminAction('rejection_reasons.delete'))
                            <button class="act-btn act-delete" title="Delete"
                                onclick="confirmDelete('{{ route('admin.rejection-reasons.destroy', $reason) }}','{{ addslashes($reason->reason) }}')">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($reasons->hasPages())
    <div class="pagination-wrap">
        <span class="pag-info">Showing {{ $reasons->firstItem() }}–{{ $reasons->lastItem() }} of {{ $reasons->total() }}</span>
        <div class="pag-links">{{ $reasons->links() }}</div>
    </div>
    @endif
</div>
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
    <h3>No rejection reasons found</h3>
    @if(auth()->user()->hasAdminAction('rejection_reasons.add'))
    <p><a href="{{ route('admin.rejection-reasons.create') }}" style="color:var(--red-lt);">Add the first reason.</a></p>
    @endif
</div>
@endif

@endsection
