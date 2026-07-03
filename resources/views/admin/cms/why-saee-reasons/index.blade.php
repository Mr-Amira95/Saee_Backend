@extends('admin.layouts.app')

@section('title', 'Reasons')
@section('page-title', 'Reasons')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.why-saee-page.index') }}">Why Sa'ee Section</a>
    <span class="sep">/</span>
    <span class="current">Reasons</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.why-saee-reasons.create') }}" class="btn-primary">+ Add New Reason</a>
</div>

@if($reasons->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Title</th>
                    <th>Subtitle</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reasons as $reason)
                <tr>
                    <td>
                        <span style="font-size: 1.5rem; background: rgba(255,255,255,.05); width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                            {{ $reason->icon ?: '⭐' }}
                        </span>
                    </td>
                    <td><div class="cell-main">{{ $reason->title['en'] ?? '' }}</div></td>
                    <td><div class="cell-sub" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $reason->subtitle['en'] ?? '' }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $reason->sort_order }}</div></td>
                    <td>
                        @if($reason->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.why-saee-reasons.edit', $reason) }}" class="act-btn act-edit" title="Edit Reason">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Reason"
                                onclick="confirmDelete('{{ route('admin.cms.why-saee-reasons.destroy', $reason) }}','{{ addslashes($reason->title['en'] ?? '') }}')">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3>No reasons found</h3>
    <p><a href="{{ route('admin.cms.why-saee-reasons.create') }}" style="color:var(--red-lt);">Create the first reason.</a></p>
</div>
@endif

@endsection
