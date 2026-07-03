@extends('admin.layouts.app')

@section('title', 'Manage Industries')
@section('page-title', 'Manage Industries')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.industries-page.index') }}">Website CMS</a>
    <span class="sep">/</span>
    <span class="current">Industries</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.industries.create') }}" class="btn-primary">+ Add New Industry</a>
</div>

@if($industries->count())
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
                @foreach($industries as $industry)
                <tr>
                    <td>
                        <span style="background: rgba(255,255,255,.05); width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                            @if($industry->icon_path)
                                <img src="{{ $industry->icon_path }}" alt="" style="width: 22px; height: 22px; object-fit: contain;">
                            @endif
                        </span>
                    </td>
                    <td><div class="cell-main">{{ $industry->title['en'] ?? '' }}</div></td>
                    <td><div class="cell-sub" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $industry->subtitle['en'] ?? '' }}</div></td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $industry->sort_order }}</div></td>
                    <td>
                        @if($industry->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.industries.edit', $industry) }}" class="act-btn act-edit" title="Edit Industry">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Industry"
                                onclick="confirmDelete('{{ route('admin.cms.industries.destroy', $industry) }}','{{ addslashes($industry->title['en'] ?? '') }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4m-4 6h.01M9 16h.01M9 8h.01"/></svg>
    <h3>No industries found</h3>
    <p><a href="{{ route('admin.cms.industries.create') }}" style="color:var(--red-lt);">Create the first industry.</a></p>
</div>
@endif

@endsection
