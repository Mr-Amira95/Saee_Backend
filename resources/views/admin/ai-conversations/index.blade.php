@extends('admin.layouts.app')

@section('title', 'AI Conversations')
@section('page-title', 'AI Conversations')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">AI Conversations</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="mini-stats">
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(99,102,241,.12)">
            <svg width="18" height="18" fill="none" stroke="#818cf8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['total']) }}</div>
            <div class="mini-stat-lbl">Total Sessions</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(34,197,94,.1)">
            <svg width="18" height="18" fill="none" stroke="#4ade80" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['today']) }}</div>
            <div class="mini-stat-lbl">Today</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(245,158,11,.1)">
            <svg width="18" height="18" fill="none" stroke="#fbbf24" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['messages']) }}</div>
            <div class="mini-stat-lbl">Total Messages</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(100,116,139,.1)">
            <svg width="18" height="18" fill="none" stroke="#94a3b8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['anon']) }}</div>
            <div class="mini-stat-lbl">Anonymous</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.ai-conversations.index') }}">
    <div class="filter-bar">
        <div class="filter-search-wrap">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search session ID or user…" class="filter-input">
        </div>
        <select name="type" class="filter-select">
            <option value="">All Sessions</option>
            <option value="authenticated" {{ request('type') === 'authenticated' ? 'selected' : '' }}>Authenticated Users</option>
            <option value="anonymous"    {{ request('type') === 'anonymous'    ? 'selected' : '' }}>Anonymous Guests</option>
        </select>
        <button type="submit" class="btn-primary" style="padding:8px 16px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter
        </button>
        @if(request('search') || request('type'))
            <a href="{{ route('admin.ai-conversations.index') }}" class="btn-secondary" style="padding:8px 14px">Clear</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Session</th>
                    <th>User</th>
                    <th>Messages</th>
                    <th>Last Activity</th>
                    <th>Started</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>
                        <div class="cell-name">
                            <div class="cell-avatar" style="background:linear-gradient(135deg,#4f46e5,#818cf8);border-radius:9px;font-size:.65rem">
                                AI
                            </div>
                            <div>
                                <div class="cell-main" style="font-family:monospace;font-size:.78rem">{{ Str::limit($session->session_id, 20, '…') }}</div>
                                <div class="cell-sub">#{{ $session->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($session->user)
                            <div class="cell-main">{{ $session->user->name }}</div>
                            <div class="cell-sub">{{ $session->user->email }}</div>
                        @else
                            <span class="badge badge-no">Anonymous</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:.88rem;font-weight:700;color:var(--text)">{{ $session->messages_count }}</span>
                        <span style="font-size:.74rem;color:var(--text-dim)"> msgs</span>
                    </td>
                    <td style="color:var(--text-sub);font-size:.82rem">
                        {{ $session->messages_max_created_at ? \Carbon\Carbon::parse($session->messages_max_created_at)->diffForHumans() : '—' }}
                    </td>
                    <td style="color:var(--text-sub);font-size:.82rem">
                        {{ $session->created_at->format('d M Y, H:i') }}
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.ai-conversations.show', $session) }}" class="act-btn act-view" title="View Conversation">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <h3>No conversations yet</h3>
                            <p>AI chatbot sessions will appear here once users start chatting.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
    <div class="pagination-wrap">
        <span class="pag-info">
            Showing {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} of {{ $sessions->total() }}
        </span>
        <div class="pag-links">
            @if($sessions->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a href="{{ $sessions->previousPageUrl() }}">‹</a>
            @endif

            @foreach($sessions->getUrlRange(max(1, $sessions->currentPage()-2), min($sessions->lastPage(), $sessions->currentPage()+2)) as $page => $url)
                @if($page == $sessions->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($sessions->hasMorePages())
                <a href="{{ $sessions->nextPageUrl() }}">›</a>
            @else
                <span class="disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
