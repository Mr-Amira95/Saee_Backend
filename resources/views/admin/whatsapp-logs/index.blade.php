@extends('admin.layouts.app')

@section('title', __('WhatsApp Messages'))
@section('page-title', __('WhatsApp Messages'))

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">{{ __('WhatsApp Messages') }}</span>
@endsection

@section('content')

{{-- Stats --}}
<div class="mini-stats">
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(34,197,94,.1)">
            <svg width="18" height="18" fill="none" stroke="#4ade80" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['conversations']) }}</div>
            <div class="mini-stat-lbl">{{ __('Conversations') }}</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(99,102,241,.12)">
            <svg width="18" height="18" fill="none" stroke="#818cf8" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['messages']) }}</div>
            <div class="mini-stat-lbl">{{ __('Total Messages') }}</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(245,158,11,.1)">
            <svg width="18" height="18" fill="none" stroke="#fbbf24" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['inbound_today']) }}</div>
            <div class="mini-stat-lbl">{{ __('Replies Today') }}</div>
        </div>
    </div>
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(239,68,68,.1)">
            <svg width="18" height="18" fill="none" stroke="#f87171" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        </div>
        <div>
            <div class="mini-stat-val">{{ number_format($stats['awaiting_reply']) }}</div>
            <div class="mini-stat-lbl">{{ __('Awaiting Reply') }}</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.whatsapp-logs.index') }}">
    <div class="filter-bar">
        <div class="filter-search-wrap">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search order number, customer name or phone…') }}" class="filter-input">
        </div>
        <select name="type" class="filter-select">
            <option value="">{{ __('All Conversations') }}</option>
            <option value="replied"  {{ request('type') === 'replied'  ? 'selected' : '' }}>{{ __('Customer Replied') }}</option>
            <option value="no_reply" {{ request('type') === 'no_reply' ? 'selected' : '' }}>{{ __('Awaiting Reply') }}</option>
        </select>
        <button type="submit" class="btn-primary" style="padding:8px 16px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            {{ __('Filter') }}
        </button>
        @if(request('search') || request('type'))
            <a href="{{ route('admin.whatsapp-logs.index') }}" class="btn-secondary" style="padding:8px 14px">{{ __('Clear') }}</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Order') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Messages') }}</th>
                    <th>{{ __('Last Message') }}</th>
                    <th>{{ __('Last Activity') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $lastLog = $order->whatsappLogs->last();
                @endphp
                <tr>
                    <td>
                        <div class="cell-main">#{{ $order->order_number }}</div>
                        <div class="cell-sub">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
                    </td>
                    <td>
                        @if($order->receiver)
                            <div class="cell-main">{{ $order->receiver->receiver_name }}</div>
                            <div class="cell-sub">{{ $order->receiver->receiver_phone }}</div>
                        @else
                            <span class="badge badge-no">{{ __('No Receiver') }}</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:.88rem;font-weight:700;color:var(--text)">{{ $order->whatsapp_logs_count }}</span>
                        <span style="font-size:.74rem;color:var(--text-dim)"> {{ __('msgs') }}</span>
                        @if($order->inbound_logs_count > 0)
                            <span class="badge badge-active" style="margin-left:6px">{{ __('Replied') }}</span>
                        @else
                            <span class="badge badge-pending" style="margin-left:6px">{{ __('No Reply') }}</span>
                        @endif
                    </td>
                    <td style="max-width:260px">
                        @if($lastLog)
                            <div class="cell-main" style="font-size:.82rem">
                                {{ $lastLog->direction === 'inbound' ? '⬅' : '➡' }}
                                {{ Str::limit($lastLog->message, 60) }}
                            </div>
                            <div class="cell-sub">{{ $lastLog->direction === 'inbound' ? __('Customer') : __('Sent') }} · {{ $lastLog->message_type ?? 'text' }}</div>
                        @else
                            <span class="cell-sub">—</span>
                        @endif
                    </td>
                    <td style="color:var(--text-sub);font-size:.82rem">
                        {{ $order->whatsapp_logs_max_created_at ? \Carbon\Carbon::parse($order->whatsapp_logs_max_created_at)->diffForHumans() : '—' }}
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.whatsapp-logs.show', $order) }}" class="act-btn act-view" title="{{ __('View Conversation') }}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                            <h3>{{ __('No WhatsApp activity yet') }}</h3>
                            <p>{{ __('Sent and received WhatsApp messages will appear here once orders start moving.') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="pagination-wrap">
        <span class="pag-info">
            {{ __('Showing') }} {{ $orders->firstItem() }}–{{ $orders->lastItem() }} {{ __('of') }} {{ $orders->total() }}
        </span>
        <div class="pag-links">
            @if($orders->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a href="{{ $orders->previousPageUrl() }}">‹</a>
            @endif

            @foreach($orders->getUrlRange(max(1, $orders->currentPage()-2), min($orders->lastPage(), $orders->currentPage()+2)) as $page => $url)
                @if($page == $orders->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}">›</a>
            @else
                <span class="disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
