@extends('admin.layouts.app')

@section('title', 'Customer Ratings Log')
@section('page-title', 'Ratings Log')

@section('breadcrumb')
    <span class="sep">/</span>
    @if(auth()->user()->hasAdminAction('reports.center'))
    <a href="{{ route('admin.reports.index') }}">Reports Center</a>
    <span class="sep">/</span>
    @endif
    <span class="current">Ratings Log</span>
@endsection

@section('head')
<style>
    .star-rating-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 8px;
        background: rgba(251,191,36,.08);
        border: 1px solid rgba(251,191,36,.14);
        color: #fbbf24;
        font-weight: 700;
        font-size: .83rem;
    }
    .ratings-filter-bar {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1.5fr 1.5fr auto;
        gap: 12px;
        align-items: end;
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
        backdrop-filter: blur(8px);
        animation: fu .45s .05s both;
    }
    @media(max-width: 1024px) {
        .ratings-filter-bar {
            grid-template-columns: 1fr 1fr;
        }
        .filter-btn-group {
            grid-column: span 2;
        }
    }
    @media(max-width: 600px) {
        .ratings-filter-bar {
            grid-template-columns: 1fr;
        }
        .filter-btn-group {
            grid-column: span 1;
        }
    }
    .filter-btn-group {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
</style>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Customer Ratings Log</h1>
            <p>Monitor customer feedback and ratings left for drivers upon order deliveries.</p>
        </div>
        <div style="display:flex;gap:10px;">
            @if(auth()->user()->hasAdminAction('reports.kpi_insights'))
            <a href="{{ route('admin.reports.kpis') }}" class="btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                KPI Insights
            </a>
            @endif
            <a href="{{ route('admin.reports.export', 'ratings') }}" class="btn-primary" style="box-shadow:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Ratings CSV
            </a>
        </div>
    </div>

    {{-- Stats Summary --}}
    <div class="mini-stats" style="margin-bottom:20px;">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(251,191,36,.12);">
                <svg width="16" height="16" fill="none" stroke="#fbbf24" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div>
                <div class="mini-stat-val" style="color:#fbbf24;display:flex;align-items:center;gap:4px;">{{ $avgAll }} ★</div>
                <div class="mini-stat-lbl">Global Average Rating</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background:rgba(59,130,246,.12);">
                <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <div class="mini-stat-val" style="color:#60a5fa;">{{ number_format($totalAll) }}</div>
                <div class="mini-stat-lbl">Total Customer Reviews</div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.reports.ratings') }}" class="ratings-filter-bar">
        <div class="form-group">
            <label class="form-label" for="driverFilter">Filter by Driver</label>
            <select name="driver" id="driverFilter" class="form-select" style="padding: 8px 30px 8px 11px;">
                <option value="">All Drivers</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ request('driver') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="ratingFilter">Filter by Rating</label>
            <select name="rating" id="ratingFilter" class="form-select" style="padding: 8px 30px 8px 11px;">
                <option value="">All Stars</option>
                @for($i=5; $i>=1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                @endfor
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="dateFrom">Date From</label>
            <input type="date" name="date_from" id="dateFrom" class="form-input" value="{{ request('date_from') }}" style="padding: 7px 11px;">
        </div>

        <div class="form-group">
            <label class="form-label" for="dateTo">Date To</label>
            <input type="date" name="date_to" id="dateTo" class="form-input" value="{{ request('date_to') }}" style="padding: 7px 11px;">
        </div>

        <div class="filter-btn-group">
            <button type="submit" class="btn-primary" style="box-shadow:none;padding:8px 16px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            @if(request()->anyFilled(['driver', 'rating', 'date_from', 'date_to']))
                <a href="{{ route('admin.reports.ratings') }}" class="btn-secondary" style="padding:8px 16px;">Clear</a>
            @endif
        </div>
    </form>

    {{-- Ratings Table Log --}}
    <div class="table-card" style="animation: fu .45s .1s both;">
        @if($ratings->isEmpty())
            <div class="empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <h3>No Ratings Found</h3>
                <p>No customer reviews match the selected filter criteria.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 120px;">Order Number</th>
                            <th>Driver</th>
                            <th style="width: 130px;">Rating</th>
                            <th>Customer Comment</th>
                            <th style="width: 160px;">Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ratings as $r)
                            <tr>
                                <td>
                                    @if($r->order)
                                        <a href="{{ route('admin.orders.show', $r->order) }}" style="color:var(--red-lt);text-decoration:none;font-weight:600;">
                                            {{ $r->order->order_number }}
                                        </a>
                                    @else
                                        <span style="color:var(--text-dim);">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($r->driver)
                                        <a href="{{ route('admin.reports.driver-kpi', $r->driver) }}" style="color:var(--text);text-decoration:none;font-weight:600;">
                                            {{ $r->driver->name }}
                                        </a>
                                        <div class="cell-sub">{{ $r->driver->phone }}</div>
                                    @else
                                        <span style="color:var(--text-dim);">Unknown Driver</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="star-rating-pill">
                                        {{ $r->rating }} ★
                                    </div>
                                </td>
                                <td>
                                    @if($r->comment)
                                        <span style="color:var(--text);font-style:italic;">"{{ $r->comment }}"</span>
                                    @else
                                        <span style="color:var(--text-dim);font-style:italic;">No text review</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="cell-main">{{ $r->created_at->format('Y-m-d') }}</div>
                                    <div class="cell-sub">{{ $r->created_at->format('H:i') }} ({{ $r->created_at->diffForHumans() }})</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                <div class="pag-info">
                    Showing {{ $ratings->firstItem() ?? 0 }} to {{ $ratings->lastItem() ?? 0 }} of {{ $ratings->total() }} ratings
                </div>
                <div class="pag-links">
                    {{ $ratings->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
