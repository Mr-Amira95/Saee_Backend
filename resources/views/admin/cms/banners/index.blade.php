@extends('admin.layouts.app')

@section('title', 'Manage Banners')
@section('page-title', 'Manage Banners')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Banners</span>
@endsection

@section('content')

<div class="filter-bar" style="justify-content: flex-end;">
    <a href="{{ route('admin.cms.banners.create') }}" class="btn-primary">+ Add New Banner</a>
</div>

@if($banners->count())
<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title & Subtitle</th>
                    <th>Link Action</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banners as $banner)
                <tr>
                    <td>
                        @if($banner->image_path)
                            <img src="{{ $banner->image_path }}" alt="Banner" style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid var(--bdr);">
                        @else
                            <div style="width: 80px; height: 45px; border-radius: 6px; background: rgba(255,255,255,.05); border: 1px dashed var(--bdr); display: flex; align-items: center; justify-content: center; font-size: .65rem; color: var(--text-dim);">No Image</div>
                        @endif
                    </td>
                    <td>
                        <div class="cell-main">{{ $banner->title }}</div>
                        @if($banner->subtitle)
                            <div class="cell-sub" style="font-size: 0.78rem;">{{ $banner->subtitle }}</div>
                        @endif
                    </td>
                    <td>
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" target="_blank" style="color: var(--info); font-size: .8rem; text-decoration: none;">
                                {{ $banner->link_text ?: 'Visit Link' }} ↗
                            </a>
                        @else
                            <span style="color: var(--text-dim); font-size: .8rem;">None</span>
                        @endif
                    </td>
                    <td><div class="cell-main" style="font-weight: 700;">{{ $banner->sort_order }}</div></td>
                    <td>
                        @if($banner->status === 'active')
                            <span class="badge-active">Active</span>
                        @else
                            <span class="badge-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.cms.banners.edit', $banner) }}" class="act-btn act-edit" title="Edit Banner">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button class="act-btn act-delete" title="Delete Banner"
                                onclick="confirmDelete('{{ route('admin.cms.banners.destroy', $banner) }}','{{ addslashes($banner->title) }}')">
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
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    <h3>No banners found</h3>
    <p><a href="{{ route('admin.cms.banners.create') }}" style="color:var(--red-lt);">Create the first banner.</a></p>
</div>
@endif

@endsection
